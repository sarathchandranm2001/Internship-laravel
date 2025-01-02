<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

class InstallerController extends Controller
{
    public function showForm()
    {
        //Ensure user is not already created
        if (User::exists()) {
            return redirect('/')->with('error', 'Application is already installed.');
        }

        return view('install');
    }


    public function processForm(Request $request)
    {
        //Ensure user is not already created
        if (User::exists()) {
            return redirect('/')->with('error', 'Application is already installed.');
        }

        try {
            // Validate input
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'auth_code' => 'required|string',
            ]);
            
            // Verify authentication code
            // include INSTALL_AUTH_CODE=RAJZrPf9gAm5EzMq9OJsIQ in .env

            if ($request->auth_code !== env('INSTALL_AUTH_CODE')) {
                return back()->with('error', 'Invalid authentication code.');
            }

            // Migrate the database
            Artisan::call('migrate', ['--force' => true]);

            // Create the admin user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'admin',
            ]);

            return redirect('/')->with('success', 'Installation completed. You can now log in.');

        } catch (\Exception $e) {
            dd('Error Occurred: ' . $e->getMessage()); // Debug exceptions
            return back()->with('error', 'Something went wrong, please try again.');
        }
    }

}
