<?php

namespace App\Http\Middleware;

use App\Models\DeliveryManDevice;
use Closure;
use Illuminate\Http\Request;

class DeliveryManAuth
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $token = explode(' ', $request->header('authorization'));
        if (count($token) > 1 && strlen($token[1]) > 30) {
            $device = DeliveryManDevice::with('deliveryMan')->where(['auth_token' => $token['1']])->first();
            if (isset($device?->deliveryMan)) {
                $request['delivery_man'] = $device->deliveryMan;
                $request['delivery_man_device'] = $device;
                return $next($request);
            }
        }

        return response()->json([
            'auth-001' => translate('Your existing session token does not authorize you any more')
        ], 401);
    }
}
