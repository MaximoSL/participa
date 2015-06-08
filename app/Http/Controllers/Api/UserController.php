<?php

namespace MXAbierto\Participa\Http\Controllers\Api;

use Illuminate\Contracts\Auth\Guard;
use MXAbierto\Participa\Models\User;
use MXAbierto\Participa\Models\DocMeta;

/**
 * 	Controller for User actions.
 */
class UserController extends AbstractApiController
{
    /**
     * Creates a new user controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getCurrent']]);
    }

    /**
     * Api route to get the session logged in user.
     *
     * @param \Illuminate\Contracts\Auth\Guard
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrent(Guard $auth)
    {
        if (!$auth->check()) {
            return response()->json(null);
        }

        return response()->json([
            'user' => $auth->user()->toArray(),
        ]);
    }

    /**
     * Returns a user.
     *
     * @param \MXAbierto\Participa\Models\User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser(User $user)
    {
        $user->load('docs', 'user_meta', 'comments', 'annotations');

        return response()->json($user);
    }

    public function getIndependentVerify()
    {
        $this->beforeFilter('admin');

        $requests = UserMeta::where('meta_key', UserMeta::TYPE_INDEPENDENT_SPONSOR)
                            ->where('meta_value', '0')
                            ->with('user')->get();

        return Response::json($requests);
    }

    public function postIndependentVerify()
    {
        $this->beforeFilter('admin');

        $request = Input::get('request');
        $status = Input::get('status');

        $user = User::find($request['user_id']);

        if (!isset($user)) {
            throw new Exception(trans('messages.user').' ('.$user->id.') '.trans('messages.notfound'));
        }

        $accepted = ['verified', 'denied'];

        if (!in_array($status, $accepted)) {
            throw new Exception('Invalid value for verify request.');
        }

        $meta = UserMeta::where('meta_key', '=', UserMeta::TYPE_INDEPENDENT_SPONSOR)
                        ->where('user_id', '=', $user->id)
                        ->first();

        if (!$meta) {
            throw new Exception(trans('messages.invalidid')." {$user->id}");
        }

        switch ($status) {
            case 'verified':

                $role = Role::where('name', 'Independent Sponsor')->first();
                if (!isset($role)) {
                    throw new Exception("Role 'Independent Sponsor' doesn't exist.");
                }

                $user->attachRole($role);

                $meta->meta_value = 1;
                $retval = $meta->save();
                break;
            case 'denied':
                $retval = $meta->delete();
                break;
        }

        return Response::json($retval);
    }

    public function getVerify()
    {
        $this->beforeFilter('admin');

        $requests = UserMeta::where('meta_key', 'verify')->with('user')->get();

        return Response::json($requests);
    }

    public function postVerify()
    {
        $this->beforeFilter('admin');

        $request = Input::get('request');
        $status = Input::get('status');

        $accepted = ['pending', 'verified', 'denied'];

        if (!in_array($status, $accepted)) {
            throw new Exception('Invalid value for verify request: '.$status);
        }

        $meta = UserMeta::find($request['id']);

        $meta->meta_value = $status;

        $ret = $meta->save();

        return Response::json($ret);
    }

    public function getAdmins()
    {
        $this->beforeFilter('admin');

        $adminRole = Role::where('name', 'Admin')->first();
        $admins = $adminRole->users()->get();

        foreach ($admins as $admin) {
            $admin->admin_contact();
        }

        return Response::json($admins);
    }

    public function postAdmin()
    {
        $admin = Input::get('admin');

        $user = User::find($admin['id']);

        if (!isset($user)) {
            throw new Exception(trans('messages.userwithid').' '.$admin['id'].' '.trans('messages.notfound'));
        }

        $user->admin_contact($admin['admin_contact']);

        return Response::json(['saved' => true]);
    }

    public function getSupport($user, $doc)
    {
        $docMeta = DocMeta::where('user_id', $user->id)->where('meta_key', '=', 'support')->where('doc_id', '=', $doc)->first();

        $supports = DocMeta::where('meta_key', '=', 'support')->where('meta_value', '=', '1')->where('doc_id', '=', $doc)->count();
        $opposes = DocMeta::where('meta_key', '=', 'support')->where('meta_value', '=', '')->where('doc_id', '=', $doc)->count();

        if (isset($docMeta)) {
            return response()->json(['support' => $docMeta->meta_value, 'supports' => $supports, 'opposes' => $opposes]);
        } else {
            return response()->json(['support' => null, 'supports' => $supports, 'opposes' => $opposes]);
        }
    }
}
