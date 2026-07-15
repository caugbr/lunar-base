<?php

namespace Plugins\Banners\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Plugins\Banners\Models\Banner;
use Plugins\Banners\Models\BannerClick;
use App\Models\Media;

class BannerController extends Controller
{
    /**
     * Listagem de banners
     */
    public function index()
    {
        $banners = Banner::withCount('clickStats')
            ->orderBy('title')
            ->get();

        return view('banners::admin.index', compact('banners'));
    }

    /**
     * Tela de criacao
     */
    public function create()
    {
        return view('banners::admin.create');
    }

    /**
     * Armazena novo banner
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'slug'      => 'required|string|max:255|unique:banners,slug|alpha_dash',
            'image_id'  => 'required|integer|exists:media,id',
            'link_url'  => 'required|url',
            'hook'      => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'target'    => 'nullable|in:_self,_blank',
            'class'     => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Banner::create($validated);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner criado com sucesso.');
    }

    /**
     * Tela de edicao
     */
    public function edit(Banner $banner)
    {
        $banner->load('image');
        return view('banners::admin.edit', compact('banner'));
    }

    /**
     * Atualiza banner
     */
    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'slug'      => "required|string|max:255|alpha_dash|unique:banners,slug,{$banner->id}",
            'image_id'  => 'required|integer|exists:media,id',
            'link_url'  => 'required|url',
            'hook'      => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'target'    => 'nullable|in:_self,_blank',
            'class'     => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $banner->update($validated);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner atualizado com sucesso.');
    }

    /**
     * Remove banner
     */
    public function destroy(Banner $banner)
    {
        $banner->delete();
        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner removido com sucesso.');
    }

    /**
     * Estatisticas detalhadas de um banner
     */
    public function stats(Banner $banner)
    {
        $banner->load('image');

        $dailyStats = $banner->getStatsByPeriod('day');
        $hourlyStats = $banner->getStatsByPeriod('hour');

        $recentClicks = $banner->clickStats()
            ->limit(50)
            ->get();

        $totalClicks = $banner->clicks;
        $todayClicks = $banner->clickStats()
            ->whereDate('clicked_at', today())
            ->count();
        $weekClicks = $banner->clickStats()
            ->whereBetween('clicked_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $monthClicks = $banner->clickStats()
            ->whereBetween('clicked_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return view('banners::admin.stats', compact(
            'banner', 'dailyStats', 'hourlyStats', 'recentClicks',
            'totalClicks', 'todayClicks', 'weekClicks', 'monthClicks'
        ));
    }

    /**
     * Redirecionamento com contagem de clique (301)
     */
    public function click(int $id)
    {
        $banner = Banner::where('id', $id)->where('is_active', true)->first();

        if (!$banner) {
            abort(404);
        }

        $banner->recordClick();

        return redirect()->away($banner->link_url, 301);
    }
}
