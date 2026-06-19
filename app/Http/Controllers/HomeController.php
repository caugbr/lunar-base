<?php

namespace App\Http\Controllers;

use App\Traits\GetSiteElements;

class HomeController extends Controller
{
    use GetSiteElements;

    public function index()
    {
        $menu = $this->buildMenu();
        $termsAndPrivacy = $this->getTermsAndPrivacyPages();

        return view('public.home', compact('menu', 'termsAndPrivacy'));
    }
}
