<?php

namespace App\Modules\Operations\Controllers;

use App\Core\Response;
use App\Modules\Operations\Services\CourseFiringService;

class CourseFiringController
{
    private $courseFiringService;

    public function __construct()
    {
        $this->courseFiringService = new CourseFiringService();
    }

    public function getCourseSequences($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;

            $courses = $this->courseFiringService->getCourseSequences($tenantId, $branchId);
            return Response::success($courses, 'Course sequences retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createCourseSequence($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            
            $required = ['tenant_id', 'branch_id', 'course_number', 'course_name'];
            foreach ($required as $field) {
                if (!isset($request[$field])) {
                    return Response::error("Missing required field: $field", 400);
                }
            }

            $courseId = $this->courseFiringService->createCourseSequence($request);
            return Response::success(['course_id' => $courseId], 'Course sequence created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateCourseSequence($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $courseId = $request['id'];
            $tenantId = $request['tenant_id'];

            $this->courseFiringService->updateCourseSequence($courseId, $tenantId, $request);
            return Response::success([], 'Course sequence updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function deleteCourseSequence($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $courseId = $request['id'];
            $tenantId = $request['tenant_id'];

            $this->courseFiringService->deleteCourseSequence($courseId, $tenantId);
            return Response::success([], 'Course sequence deleted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createOrderCourses($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $orderId = $request['order_id'];
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'];

            $count = $this->courseFiringService->createOrderCourses($orderId, $tenantId, $branchId);
            return Response::success(['courses_created' => $count], 'Order courses created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function fireCourse($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $orderCourseId = $request['id'];
            $tenantId = $request['tenant_id'];
            $firedBy = $request['fired_by'] ?? null;

            $this->courseFiringService->fireCourse($orderCourseId, $tenantId, $firedBy);
            return Response::success([], 'Course fired successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function completeCourse($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $orderCourseId = $request['id'];
            $tenantId = $request['tenant_id'];

            $this->courseFiringService->completeCourse($orderCourseId, $tenantId);
            return Response::success([], 'Course completed successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getOrderCourses($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $orderId = $request['order_id'];

            $courses = $this->courseFiringService->getOrderCourses($orderId);
            return Response::success($courses, 'Order courses retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function checkAutoFireCourses($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            
            $this->courseFiringService->checkAutoFireCourses();
            return Response::success([], 'Auto-fire courses checked successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
