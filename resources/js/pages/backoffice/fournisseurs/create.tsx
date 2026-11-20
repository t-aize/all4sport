import { store } from '@/actions/App/Http/Controllers/BackOffice/FournisseurController';
import { FournisseurForm } from '@/components/fournisseur-form';
import BackOfficeLayout from '@/layouts/back-office-layout';

/**
 * Création d'un fournisseur (route « backoffice.fournisseurs.create »).
 */
export default function CreateFournisseur() {
    return (
        <BackOfficeLayout title="Nouveau fournisseur">
            <div className="bg-card rounded-3xl border p-6">
                <FournisseurForm
                    action={store.url()}
                    method="post"
                    libelleBouton="Créer le fournisseur"
                />
            </div>
        </BackOfficeLayout>
    );
}
