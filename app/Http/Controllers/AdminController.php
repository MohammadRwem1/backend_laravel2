<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminController extends Controller
{
    public function pendingUsers()
    {
        $users = User::where('status', 'pending')->get();

        return view('admin.pending-users', compact('users'));
    }

    public function approve($id)
    {
        User::where('id', $id)->update(['status' => 'approved']);

        return redirect()->back();
    }

    public function reject($id)
    {
        User::where('id', $id)->update(['status' => 'rejected']);

        return redirect()->back();
    }
}
