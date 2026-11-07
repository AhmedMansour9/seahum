<?php

namespace App\Http\Middleware;

use App\Enums\DeliveryManAppRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows a delivery man app request only for the given app roles, e.g. `delivery_man_role:admin,customer_service`.
 * Must run after `delivery_man_auth`, which puts the authenticated delivery man on the request.
 */
class DeliveryManAppRoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $appRole = $request['delivery_man']?->app_role ?? DeliveryManAppRole::DeliveryMan;

        if (!in_array($appRole->value, $allowedRoles, true)) {
            return response()->json([
                'errors' => [['code' => 'role-001', 'message' => translate('You_are_not_allowed_to_access_this_section')]],
            ], 403);
        }

        return $next($request);
    }
}
