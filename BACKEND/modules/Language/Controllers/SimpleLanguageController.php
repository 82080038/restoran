<?php

require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';

/**
 * Simple Language Controller (compatible with current router pattern)
 * Manages multi-language / i18n translations
 */
class SimpleLanguageController
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Get all available languages
     * GET /api/v1/languages
     */
    public function getLanguages($request)
    {
        try {
            $pdo = $this->db->connect();

            $stmt = $pdo->prepare("SELECT * FROM languages WHERE is_active = 1 ORDER BY is_default DESC, language_name ASC");
            $stmt->execute();
            $languages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($languages, 'Languages retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * Get translations for a language
     * GET /api/v1/languages/{code}/translations
     */
    public function getTranslations($request)
    {
        try {
            $pdo = $this->db->connect();
            $code = $request['code'] ?? ($request['params']['code'] ?? '');
            $context = $request['query']['context'] ?? null;

            if (empty($code)) {
                return Response::error('Language code is required', 400);
            }

            $sql = "SELECT t.translation_key, t.translation_value, t.context
                    FROM translations t
                    INNER JOIN languages l ON t.language_id = l.language_id
                    WHERE l.language_code = ? AND l.is_active = 1";
            $params = [$code];

            if ($context) {
                $sql .= " AND (t.context = ? OR t.context IS NULL)";
                $params[] = $context;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $translations = [];
            foreach ($results as $row) {
                $translations[$row['translation_key']] = $row['translation_value'];
            }

            return Response::success([
                'language_code' => $code,
                'translations' => $translations,
                'count' => count($translations)
            ], 'Translations retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * Get user language preference
     * GET /api/v1/languages/preference
     */
    public function getUserPreference($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();

            $stmt = $pdo->prepare("
                SELECT ulp.language_code, l.language_name, l.native_name, l.flag_icon
                FROM user_language_preferences ulp
                INNER JOIN languages l ON ulp.language_id = l.language_id
                WHERE ulp.user_id = ?
            ");
            $stmt->execute([$payload['user_id']]);
            $preference = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$preference) {
                // Return default language
                $stmt = $pdo->prepare("SELECT language_code, language_name, native_name, flag_icon FROM languages WHERE is_default = 1 AND is_active = 1 LIMIT 1");
                $stmt->execute();
                $preference = $stmt->fetch(\PDO::FETCH_ASSOC);
                $preference['is_default'] = true;
            }

            return Response::success($preference, 'Language preference retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * Set user language preference
     * POST /api/v1/languages/preference
     */
    public function setUserPreference($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $languageCode = $body['language_code'] ?? '';

            if (empty($languageCode)) {
                return Response::error('language_code is required', 400);
            }

            // Get language ID
            $stmt = $pdo->prepare("SELECT language_id FROM languages WHERE language_code = ? AND is_active = 1");
            $stmt->execute([$languageCode]);
            $lang = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$lang) {
                return Response::error('Language not found or inactive', 404);
            }

            // Upsert preference
            $stmt = $pdo->prepare("
                INSERT INTO user_language_preferences (user_id, language_id, updated_at)
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE language_id = VALUES(language_id), updated_at = NOW()
            ");
            $stmt->execute([$payload['user_id'], $lang['language_id']]);

            return Response::success([
                'language_code' => $languageCode
            ], 'Language preference updated');
        } catch (\Exception $e) {
            return Response::error('Failed to set preference: ' . $e->getMessage());
        }
    }

    /**
     * Get all translations (admin - for management)
     * GET /api/v1/languages/translations/all
     */
    public function getAllTranslations($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $code = $request['query']['language_code'] ?? null;
            $context = $request['query']['context'] ?? null;
            $page = (int)($request['query']['page'] ?? 1);
            $limit = (int)($request['query']['limit'] ?? 50);
            $offset = ($page - 1) * $limit;

            $sql = "SELECT t.translation_id, t.translation_key, t.translation_value, t.context,
                           l.language_code, l.language_name
                    FROM translations t
                    INNER JOIN languages l ON t.language_id = l.language_id";
            $where = [];
            $params = [];

            if ($code) {
                $where[] = "l.language_code = ?";
                $params[] = $code;
            }
            if ($context) {
                $where[] = "t.context = ?";
                $params[] = $context;
            }

            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }

            // Count total
            $countSql = "SELECT COUNT(*) as total FROM translations t INNER JOIN languages l ON t.language_id = l.language_id";
            if (!empty($where)) {
                $countSql .= " WHERE " . implode(' AND ', $where);
            }
            $stmt = $pdo->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $sql .= " ORDER BY t.translation_key ASC LIMIT $limit OFFSET $offset";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $translations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::paginated($translations, $total, $page, $limit, 'Translations retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * Add or update a translation
     * POST /api/v1/languages/translations
     */
    public function saveTranslation($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];

            $languageCode = $body['language_code'] ?? '';
            $key = $body['translation_key'] ?? '';
            $value = $body['translation_value'] ?? $body['translated_value'] ?? '';
            $context = $body['context'] ?? null;

            if (empty($languageCode) || empty($key) || empty($value)) {
                return Response::error('language_code, translation_key, and translation_value are required', 400);
            }

            // Get language ID
            $stmt = $pdo->prepare("SELECT language_id FROM languages WHERE language_code = ?");
            $stmt->execute([$languageCode]);
            $lang = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$lang) {
                return Response::error('Language not found', 404);
            }

            // Upsert translation
            $stmt = $pdo->prepare("
                INSERT INTO translations (language_id, translation_key, translation_value, context, updated_at)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE translation_value = VALUES(translation_value), context = VALUES(context), updated_at = NOW()
            ");
            $stmt->execute([$lang['language_id'], $key, $value, $context]);

            return Response::success([
                'language_code' => $languageCode,
                'translation_key' => $key
            ], 'Translation saved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to save translation: ' . $e->getMessage());
        }
    }

    /**
     * Delete a translation
     * DELETE /api/v1/languages/translations/{id}
     */
    public function deleteTranslation($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $translationId = $request['id'] ?? 0;

            $stmt = $pdo->prepare("DELETE FROM translations WHERE translation_id = ?");
            $stmt->execute([$translationId]);

            if ($stmt->rowCount() === 0) {
                return Response::notFound('Translation not found');
            }

            return Response::success([], 'Translation deleted');
        } catch (\Exception $e) {
            return Response::error('Failed to delete translation: ' . $e->getMessage());
        }
    }

    /**
     * Get translation contexts/groups
     * GET /api/v1/languages/contexts
     */
    public function getContexts($request)
    {
        try {
            $pdo = $this->db->connect();

            $stmt = $pdo->prepare("SELECT DISTINCT context FROM translations WHERE context IS NOT NULL ORDER BY context");
            $stmt->execute();
            $contexts = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            return Response::success($contexts, 'Translation contexts retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }
}
