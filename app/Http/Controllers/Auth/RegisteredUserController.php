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
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->accounts()->create([
            'name' => 'Hauptkonto',
            'currency' => 'CHF',
        ]);

        collect([
            ['name' => 'Lohn', 'type' => 'income', 'color' => '#059669', 'icon' => 'Briefcase'],
            ['name' => 'Freelancer', 'type' => 'income', 'color' => '#2563eb', 'icon' => 'Laptop'],
            ['name' => 'Rechnungen', 'type' => 'income', 'color' => '#0ea5e9', 'icon' => 'ReceiptText'],
            ['name' => 'Miete', 'type' => 'expense', 'color' => '#dc2626', 'icon' => 'Home'],
            ['name' => 'Lebensmittel', 'type' => 'expense', 'color' => '#ea580c', 'icon' => 'ShoppingCart'],
        ])->each(fn (array $category) => $user->categories()->create($category));

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
