<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switchLang($locale)
    {
        if (in_array($locale, ['en', 'id'])) {
            session(['locale' => $locale]);
            
            if (auth()->check()) {
                $user = auth()->user();
                $user->preferred_locale = $locale;
                $user->save();
            }
        }
        
        return redirect()->back();
    }
}
