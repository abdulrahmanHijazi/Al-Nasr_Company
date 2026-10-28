<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function index() { return Inertia::render('Contact/Index'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:30',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|min:5',
        ]);
        ContactMessage::create($data);

        return back()->with('success', 'Message sent successfully.');
    }
}
