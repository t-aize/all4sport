<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFournisseurRequest;
use App\Http\Requests\UpdateFournisseurRequest;
use App\Models\Fournisseur;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FournisseurController extends Controller
{
    public function index(): View
    {
        $fournisseurs = Fournisseur::query()
            ->withCount('produits')
            ->orderBy('nom_fournisseur')
            ->paginate(15);

        return view('fournisseurs.index', compact('fournisseurs'));
    }

    public function create(): View
    {
        return view('fournisseurs.create');
    }

    public function store(StoreFournisseurRequest $request): RedirectResponse
    {
        Fournisseur::create($request->validated());

        return redirect()->route('fournisseurs.index')
            ->with('success', 'Fournisseur créé.');
    }

    public function edit(Fournisseur $fournisseur): View
    {
        return view('fournisseurs.edit', compact('fournisseur'));
    }

    public function update(UpdateFournisseurRequest $request, Fournisseur $fournisseur): RedirectResponse
    {
        $fournisseur->update($request->validated());

        return redirect()->route('fournisseurs.index')
            ->with('success', 'Fournisseur modifié.');
    }

    public function destroy(Fournisseur $fournisseur): RedirectResponse
    {
        if ($fournisseur->produits()->exists()) {
            return back()->with('error', 'Impossible de supprimer un fournisseur qui a des produits.');
        }

        $fournisseur->delete();

        return redirect()->route('fournisseurs.index')
            ->with('success', 'Fournisseur supprimé.');
    }
}
