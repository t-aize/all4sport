import { Head } from '@inertiajs/react';
import { type ReactNode } from 'react';

import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

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
