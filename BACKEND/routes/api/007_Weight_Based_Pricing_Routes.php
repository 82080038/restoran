<?php

// Weight-Based Pricing Routes
$router->addRoute('POST', '/api/v1/sales/weight-based/calculate-price', function($request) use ($weightBasedPricingController) {
    return $weightBasedPricingController->calculatePrice($request);
});
$router->addRoute('GET', '/api/v1/sales/weight-based/inventory-items', function($request) use ($weightBasedPricingController) {
    return $weightBasedPricingController->getInventoryItems($request);
});
$router->addRoute('GET', '/api/v1/sales/weight-based/pricing-config', function($request) use ($weightBasedPricingController) {
    return $weightBasedPricingController->getPricingConfig($request);
});
$router->addRoute('PUT', '/api/v1/sales/weight-based/pricing-config', function($request) use ($weightBasedPricingController) {
    return $weightBasedPricingController->updatePricingConfig($request);
});
$router->addRoute('POST', '/api/v1/sales/weight-based/reserve-item', function($request) use ($weightBasedPricingController) {
    return $weightBasedPricingController->reserveItem($request);
});
$router->addRoute('POST', '/api/v1/sales/weight-based/mark-as-sold', function($request) use ($weightBasedPricingController) {
    return $weightBasedPricingController->markAsSold($request);
});

// Menu Routes - Categories (with permission check)
$router->addRoute('GET', '/api/v1/menu/categories', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getCategories($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu/categories/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getCategory($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/menu/categories', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->createCategory($request);
    },
    'MENU_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/menu/categories/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->updateCategory($request);
    },
    'MENU_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/menu/categories/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->deleteCategory($request);
    },
    'MENU_DELETE',
    $permissionMiddleware,
    $authMiddleware
));

// Menu Routes - Products (with permission check)
$router->addRoute('GET', '/api/v1/menu/products', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getProducts($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu/products/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getProduct($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/menu/products', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->createProduct($request);
    },
    'MENU_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/menu/products/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->updateProduct($request);
    },
    'MENU_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/menu/products/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->deleteProduct($request);
    },
    'MENU_DELETE',
    $permissionMiddleware,
    $authMiddleware
));

// Menu Routes - Product Variants
$router->addRoute('POST', '/api/v1/menu/products/{id}/variants', function($request) use ($productVariantController) {
    return $productVariantController->create($request);
});
$router->addRoute('GET', '/api/v1/menu/products/{id}/variants', function($request) use ($productVariantController) {
    return $productVariantController->getByProduct($request);
});
$router->addRoute('PUT', '/api/v1/menu/variants/{id}', function($request) use ($productVariantController) {
    return $productVariantController->update($request);
});
$router->addRoute('DELETE', '/api/v1/menu/variants/{id}', function($request) use ($productVariantController) {
    return $productVariantController->delete($request);
});

// Menu Routes - Product Modifiers
$router->addRoute('POST', '/api/v1/menu/modifier-groups', function($request) use ($productModifierController) {
    return $productModifierController->createGroup($request);
});
$router->addRoute('GET', '/api/v1/menu/modifier-groups', function($request) use ($productModifierController) {
    return $productModifierController->getGroups($request);
});
$router->addRoute('POST', '/api/v1/menu/modifier-groups/{id}/modifiers', function($request) use ($productModifierController) {
    return $productModifierController->createModifier($request);
});
$router->addRoute('GET', '/api/v1/menu/modifier-groups/{id}/modifiers', function($request) use ($productModifierController) {
    return $productModifierController->getModifiersByGroup($request);
});
$router->addRoute('POST', '/api/v1/menu/products/{id}/modifiers', function($request) use ($productModifierController) {
    return $productModifierController->assignToProduct($request);
});
$router->addRoute('GET', '/api/v1/menu/products/{id}/modifiers', function($request) use ($productModifierController) {
    return $productModifierController->getProductModifiers($request);
});

// Menu Routes - Combos
$router->addRoute('POST', '/api/v1/menu/combos', function($request) use ($comboController) {
    return $comboController->create($request);
});
$router->addRoute('GET', '/api/v1/menu/combos', function($request) use ($comboController) {
    return $comboController->getAll($request);
});
$router->addRoute('POST', '/api/v1/menu/combos/{id}/calculate-price', function($request) use ($comboController) {
    return $comboController->calculatePrice($request);
});

// Menu Routes - Recipes
$router->addRoute('GET', '/api/v1/menu/recipes', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getRecipes($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu/recipes/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getRecipe($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/menu/recipes', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->createRecipe($request);
    },
    'MENU_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/menu/recipes/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->updateRecipe($request);
    },
    'MENU_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/menu/recipes/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->deleteRecipe($request);
    },
    'MENU_DELETE',
    $permissionMiddleware,
    $authMiddleware
));

// Recipe Management Routes (New Module)
$router->addRoute('GET', '/api/v1/recipes', withAuth(
    function($request) use ($recipeController) {
        return $recipeController->index($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/recipes/{id}', withAuth(
    function($request) use ($recipeController) {
        return $recipeController->show($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/recipes', withAuth(
    function($request) use ($recipeController) {
        return $recipeController->create($request);
    },
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/recipes/{id}', withAuth(
    function($request) use ($recipeController) {
        return $recipeController->update($request);
    },
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/recipes/{id}', withAuth(
    function($request) use ($recipeController) {
        return $recipeController->delete($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/recipes/{id}/cost-analysis', withAuth(
    function($request) use ($recipeController) {
        return $recipeController->costAnalysis($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/recipes/{id}/clone', withAuth(
    function($request) use ($recipeController) {
        return $recipeController->clone($request);
    },
    $authMiddleware
));

