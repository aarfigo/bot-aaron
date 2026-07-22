<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Models\Staff;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        $staff = Staff::all();
        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        return view('admin.staff.create');
    }

    public function store(StoreStaffRequest $request)
    {
        $data = $request->validated();
        // Hash password if provided
        if(!empty($data['password'])){
            $data['password'] = bcrypt($data['password']);
        }
        // Ensure required DB columns have sensible defaults if not provided by the request
        if (empty($data['status'])) {
            $data['status'] = 'active';
        }
        if (empty($data['role'])) {
            // Default to 'mesero' (canonical Spanish role). Legacy 'staff' values are still supported in migration code.
            $data['role'] = 'mesero';
        }

        $s = Staff::create($data);

        // Sync to users table so created staff can login immediately.
        try {
            $username = $s->username;
            $email = null;
            if (str_contains($username, '@')) {
                $email = $username;
            } else {
                $email = $username . '@staff.local';
                if (User::where('email', $email)->exists()) {
                    $email = $username . '+' . $s->staffID . '@staff.local';
                }
            }

            $userData = [
                'name' => $s->staffName ?? $username,
                'email' => $email,
                'role' => $s->role ?? 'mesero',
            ];
            if (!empty($data['password'])) {
                // $data['password'] is already bcrypt'ed for tbl_staff; hash a fresh value for users table
                $userData['password'] = Hash::make($request->string('password'));
            }

            User::updateOrCreate([
                'username' => $username,
            ], $userData);
        } catch (\Exception $e) {
            // Don't block staff creation for transient user sync errors; intentionally swallow here.
        }

        return redirect()->route('admin.staff.index')->with('success','Staff creado');
    }

    public function edit($id)
    {
        $s = Staff::findOrFail($id);
        return view('admin.staff.edit', ['staff' => $s]);
    }

    public function update(StoreStaffRequest $request, $id)
    {
        $s = Staff::findOrFail($id);
        $data = $request->validated();
        if(empty($data['password'])){
            unset($data['password']);
        } else {
            $data['password'] = bcrypt($data['password']);
        }
        $s->update($data);

        // Sync updated fields to users table
        try {
            $username = $s->username;
            $email = null;
            if (str_contains($username, '@')) {
                $email = $username;
            } else {
                $email = $username . '@staff.local';
                if (User::where('email', $email)->exists()) {
                    $email = $username . '+' . $s->staffID . '@staff.local';
                }
            }

            $userData = [
                'name' => $s->staffName ?? $username,
                'email' => $email,
                'role' => $s->role ?? 'mesero',
            ];
            if (isset($data['password'])) {
                $userData['password'] = Hash::make($request->string('password'));
            }

            User::updateOrCreate(['username' => $username], $userData);
        } catch (\Exception $e) {
            // ignore sync errors to avoid breaking admin flow
        }

        return redirect()->route('admin.staff.index')->with('success','Staff actualizado');
    }

    public function destroy($id)
    {
        $s = Staff::findOrFail($id);
        $s->delete();
        return redirect()->route('admin.staff.index')->with('success','Staff eliminado');
    }
}
