<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Validate employee exists in employee database and is active
        $employee = \DB::connection('mysql')
            ->table('db_1257_employees')
            ->where('workemail', $request->email)
            ->whereNotIn('EmployeeStatus', ['Terminated', 'Suspended'])
            ->first();

        if (!$employee) {
            return back()->withErrors([
                'email' => 'This email is not authorized to register. Only active employees can create accounts.',
            ])->withInput($request->only('email'));
        }

        // Create user with employee information
        $user = User::create([
            'name' => $employee->EmployeeName,
            'email' => $employee->workemail,
            'username' => $employee->EmployeeUserName,
            'employee_id' => $employee->id,
            'is_admin' => strtolower($employee->IsAdmin) === 'yes',
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Update last login time
        $user->update(['last_login_at' => now()]);

        return redirect(route('conversations.index', absolute: false));
    }
}
