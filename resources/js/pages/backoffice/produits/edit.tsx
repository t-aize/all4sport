import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import ProduitForm, {
    type MagasinStock,
    type Option,
    type ProduitExistant,
} from '@/components/produit-form';
import BackOfficeLayout from '@/layouts/back-office-layout';
import { update } from '@/actions/App/Http/Controllers/BackOffice/ProduitController';

/**
 * Modification d'un produit (route "backoffice.produits.edit"). La référence
 * ne peut pas être modifiée.
 *
 * @param roleLibelle Nom du rôle de l'employé connecté.
 * @param produit Le produit à modifier, avec ses photos et son stock par magasin.
 * @param fournisseurs Fournisseurs proposés.
 * @param rayons Rayons proposés.
 * @param magasins Magasins dont on saisit le stock.
 */
export default function EditProduit({
    roleLibelle,
    produit,
    fournisseurs,
    rayons,
    magasins,
}: {
    roleLibelle: string;
    produit: ProduitExistant;
    fournisseurs: Option[];
    rayons: Option[];
    magasins: MagasinStock[];
}) {
    // `update.form()` renvoie l'adresse du produit ; Laravel reçoit la requête
    // en POST avec "_method=PUT", ce qui permet d'envoyer des fichiers.
    const formulaire = update.form(produit.reference);

    return (
        <BackOfficeLayout
            title={`Modifier ${produit.reference}`}
            roleLibelle={roleLibelle}
        >
            <Card>
                <CardHeader>
                    <CardTitle>Modifier le produit</CardTitle>
                    <CardDescription>
                        Référence : {produit.reference}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <ProduitForm
                        action={formulaire.action}
                        method="post"
                        fournisseurs={fournisseurs}
                        rayons={rayons}
                        magasins={magasins}
                        produit={produit}
                        libelleBouton="Enregistrer les modifications"
                    />
                </CardContent>
            </Card>
        </BackOfficeLayout>
    );
}
