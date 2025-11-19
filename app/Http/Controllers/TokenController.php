<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TokenController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tokens = $user->tokens()->latest()->get();

        return view('tokens.index', [
            'tokens' => $tokens,
            'allowedAbilities' => $user->allowedTokenAbilities(),
            'authRole' => $user->role?->value ?? ($user->isAdmin() ? 'admin' : 'user'),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $allowed = $user->allowedTokenAbilities();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => [Rule::in($allowed)],
        ]);

        $abilities = array_values(array_unique($validated['abilities'] ?? []));

        // Ensure abilities are subset of allowed
        $abilities = array_values(array_intersect($abilities, $allowed));

        $token = $user->createToken($validated['name'], $abilities);

        return redirect()->route('tokens.index')
            ->with('status', 'token-created')
            ->with('plainTextToken', $token->plainTextToken);
    }

    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        $token = $user->tokens()->where('id', $id)->firstOrFail();
        $token->delete();

        return redirect()->route('tokens.index')->with('status', 'token-revoked');
    }
}
