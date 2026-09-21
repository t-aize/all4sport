import { Head } from '@inertiajs/react';
import { type ReactNode } from 'react';

import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

/**
 * Mise en page commune aux pages d'authentification (connexion, inscription,
 * mot de passe oublié, réinitialisation) : une carte centrée sur un fond gris.
 *
 * Un « layout » évite de recopier la même structure dans chaque page : la page
 * fournit seulement son titre, sa description et son formulaire (`children`).
 *
 * @param title Titre affiché dans la carte et dans l'onglet du navigateur.
 * @param description Phrase d'explication sous le titre.
 * @param children Contenu de la page (le formulaire).
 * @param className Classes CSS optionnelles pour changer la largeur de la carte.
 */
export default function AuthLayout({
    title,
    description,
    children,
    className,
}: {
    title: string;
    description: string;
    children: ReactNode;
    className?: string;
}) {
    return (
        <div className="bg-muted flex min-h-screen flex-col items-center justify-center gap-6 p-6">
            {/* `Head` modifie le <title> de la page depuis React. */}
            <Head title={title} />
            <div
                className={`flex w-full max-w-sm flex-col gap-6 ${className ?? ''}`}
            >
                <Card>
                    <CardHeader className="text-center">
                        <CardTitle className="text-xl">{title}</CardTitle>
                        <CardDescription>{description}</CardDescription>
                    </CardHeader>
                    <CardContent>{children}</CardContent>
                </Card>
            </div>
        </div>
    );
}
