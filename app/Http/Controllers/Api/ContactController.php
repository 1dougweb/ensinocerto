<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function checkExistingContact(Request $request)
    {
        // Implementation for checking existing contact
        return response()->json(['exists' => false]);
    }
} 