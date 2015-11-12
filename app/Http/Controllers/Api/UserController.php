<?php

namespace MXAbierto\Participa\Http\Controllers\Api;

use GrahamCampbell\Binput\Facades\Binput;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use MXAbierto\Participa\Models\DocMeta;
use MXAbierto\Participa\Models\User;
use MXAbierto\Participa\Models\UserMeta;

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

        return response()->json($requests);
    }

    public function postIndependentVerify()
    {
        $this->beforeFilter('admin');

        $request = Binput::get('request');
        $status = Binput::get('status');

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

        return response()->json($retval);
    }

    public function getVerify()
    {
        $this->beforeFilter('admin');

        $requests = UserMeta::where('meta_key', 'verify')->with('user')->get();

        return response()->json($requests);
    }

    public function postVerify()
    {
        $this->beforeFilter('admin');

        $request = Binput::get('request');
        $status = Binput::get('status');

        $accepted = ['pending', 'verified', 'denied'];

        if (!in_array($status, $accepted)) {
            throw new Exception('Invalid value for verify request: '.$status);
        }

        $meta = UserMeta::find($request['id']);

        $meta->meta_value = $status;

        $ret = $meta->save();

        return response()->json($ret);
    }

    public function getAdmins()
    {
        $this->beforeFilter('admin');

        $adminRole = Role::where('name', 'Admin')->first();
        $admins = $adminRole->users()->get();

        foreach ($admins as $admin) {
            $admin->admin_contact();
        }

        return response()->json($admins);
    }

    public function postAdmin()
    {
        $admin = Binput::get('admin');

        $user = User::find($admin['id']);

        if (!isset($user)) {
            throw new Exception(trans('messages.userwithid').' '.$admin['id'].' '.trans('messages.notfound'));
        }

        $user->admin_contact($admin['admin_contact']);

        return response()->json(['saved' => true]);
    }

    /**
     * Get the user support status.
     *
     * @param int $doc
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSupport($doc)
    {
        $user = Auth::user();

        $docMeta = DocMeta::where('user_id', $user->id)->where('meta_key', '=', 'support')->where('doc_id', '=', $doc)->first();

        $supports = DocMeta::where('meta_key', '=', 'support')->where('meta_value', '=', '1')->where('doc_id', '=', $doc)->count();
        $opposes = DocMeta::where('meta_key', '=', 'support')->where('meta_value', '=', '')->where('doc_id', '=', $doc)->count();

        if (isset($docMeta)) {
            return response()->json(['support' => $docMeta->meta_value, 'supports' => $supports, 'opposes' => $opposes]);
        } else {
            return response()->json(['support' => null, 'supports' => $supports, 'opposes' => $opposes]);
        }
    }

    /**
     * Method to handle posting support/oppose clicks on a document.
     *
     * @param int $doc
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function postSupport($doc)
    {
        $input = Binput::all();

        $supported = (bool) $input['support'];

        $docMeta = DocMeta::withTrashed()->where('user_id', Auth::user()->id)->where('meta_key', '=', 'support')->where('doc_id', '=', $doc)->first();

        if (!isset($docMeta)) {
            $docMeta = new DocMeta();
            $docMeta->doc_id = $doc;
            $docMeta->user_id = Auth::user()->id;
            $docMeta->meta_key = 'support';
            $docMeta->meta_value = (string) $supported;
            $docMeta->save();
        } elseif ($docMeta->meta_value == (string) $supported && !$docMeta->trashed()) {
            $docMeta->delete();
            $supported = null;
        } else {
            if ($docMeta->trashed()) {
                $docMeta->restore();
            }
            $docMeta->doc_id = $doc;
            $docMeta->user_id = Auth::user()->id;
            $docMeta->meta_key = 'support';
            $docMeta->meta_value = (string) (bool) $input['support'];
            $docMeta->save();
        }

        $supports = DocMeta::where('meta_key', '=', 'support')->where('meta_value', '=', '1')->where('doc_id', '=', $doc)->count();
        $opposes = DocMeta::where('meta_key', '=', 'support')->where('meta_value', '=', '')->where('doc_id', '=', $doc)->count();

        return response()->json(['support' => $supported, 'supports' => $supports, 'opposes' => $opposes]);
    }

    /**
     *	Api route to edit user's email.
     *
     * @param \MXAbierto\Participa\Models\User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function editEmail(User $user)
    {
        //Check authorization
        if (Auth::user()->id !== $user->id) {
            return response()->json($this->growlMessage('No estás autorizado a cambiar el email del usuario', 'error'));
        }

        $user->email = Binput::get('email');
        $user->password = Binput::get('password');

        if (!$user->save()) {
            $errors = $user->getErrors();
            $messages = [];

            foreach ($errors->all() as $error) {
                array_push($messages, $error);
            }

            return response()->json($this->growlMessage($messages, 'error'), 500);
        }

        return response()->json($this->growlMessage('Email guardado exitosamente.  Gracias.', 'success'), 200);
    }
}
