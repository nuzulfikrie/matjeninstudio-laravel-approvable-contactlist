<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MatJeninStudio\ContactApprovable\Models\Contact;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::with('users')->paginate(15);

        return view('contact-approvable::contacts.index', compact('contacts'));
    }

    public function show(Contact $contact)
    {
        $contact->load(['users', 'approvals']);

        return view('contact-approvable::contacts.show', compact('contact'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $contact = Contact::create($validated);

        return redirect()->route('contact-approvable.contacts.show', $contact)
            ->with('success', 'Contact created successfully.');
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $contact->update($validated);

        return redirect()->route('contact-approvable.contacts.show', $contact)
            ->with('success', 'Contact updated successfully.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('contact-approvable.contacts.index')
            ->with('success', 'Contact deleted successfully.');
    }
}
