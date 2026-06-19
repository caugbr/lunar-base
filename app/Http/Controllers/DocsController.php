<?php

namespace App\Http\Controllers;

use App\Traits\GetSiteElements;

class DocsController extends Controller
{
    use GetSiteElements;

    public function index()
    {
        $menu = $this->buildMenu();
        $termsAndPrivacy = $this->getTermsAndPrivacyPages();

        return view('public.docs.index', compact('menu', 'termsAndPrivacy'));
    }
}
