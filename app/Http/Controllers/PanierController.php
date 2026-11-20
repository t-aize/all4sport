<?php
 
namespace App\Http\Controllers;
 
use App\Models\Produit;
use App\Services\ServicePanier;
use Illuminate\Http\Request;
 
class PanierController extends Controller
{
    public function __construct(private ServicePanier $servicePanier)
    {
    }
 
    public function index()
    {
        return view('panier.index', ['panier' => $this->servicePanier->courant()]);
    }
 
    public function ajouter(Request $request, Produit $produit)
    {
        $donnees = $request->validate(['quantite' => 'nullable|integer|min:1|max:99']);
 
        $this->servicePanier->ajouter($produit, $donnees['quantite'] ?? 1);
 
        return back()->with('succes', 'Produit ajouté au panier.');
    }
 
    public function modifier(Request $request, Produit $produit)
    {
        $donnees = $request->validate(['quantite' => 'required|integer|min:0|max:99']);
 
        $this->servicePanier->modifier($produit, $donnees['quantite']);
 
        return back()->with('succes', 'Panier mis à jour.');
    }
 
    public function retirer(Produit $produit)
    {
        $this->servicePanier->retirer($produit);
 
        return back()->with('succes', 'Produit retiré du panier.');
    }
 
    public function vider()
    {
        $this->servicePanier->vider();
 
        return back()->with('succes', 'Panier vidé.');
    }
}
 