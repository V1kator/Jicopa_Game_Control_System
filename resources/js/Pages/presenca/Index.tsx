import { Head, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import ProfessorLayout from '@/Layouts/ProfessorLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/Components/ui/dialog';
import { Label } from '@/Components/ui/label';
import { useState } from 'react';
import { PageProps } from '@/types';
import { cn } from '@/lib/utils';
import ConfirmDialog from '@/Components/shared/ConfirmDialog';
import { X } from 'lucide-react';

interface Jogo {
    id: number;
    categoria: { name: string };
    esporte: { name: string; type: string };
    time1?: { id: number; name: string; period: string };
    time2?: { id: number; name: string; period: string };
    data: string;
    hora: string;
    local: string;
}

interface Aluno {
    id: number;
    name: string;
    turma: { name: string; period: string };
}

interface Presenca {
    id: number;
    aluno_id: number;
    presente: boolean;
    is_substituto: boolean;
    substituto_de_time_id: number | null;
    aluno: Aluno;
}

interface Props extends PageProps {
    jogo: Jogo;
    atletasEsperados: Aluno[];
    presencas: Presenca[];
    todosAlunos: Aluno[];
}

export default function Index({ auth, jogo, atletasEsperados, presencas, todosAlunos }: Props) {
    const Layout = auth.user.roles.includes('admin') ? AdminLayout : ProfessorLayout;

    // Initialize attendance state from existing presencas
    const [attendance, setAttendance] = useState<Record<number, { presente: boolean; is_substituto: boolean; substituto_de_time_id: number | null }>>(() => {
        const initial: Record<number, { presente: boolean; is_substituto: boolean; substituto_de_time_id: number | null }> = {};

        // Pre-fill expected athletes as present by default
        atletasEsperados.forEach(atleta => {
            const existing = presencas.find(p => p.aluno_id === atleta.id);
            initial[atleta.id] = {
                presente: existing?.presente ?? true,
                is_substituto: false,
                substituto_de_time_id: null,
            };
        });

        // Add existing presencas (including substitutos)
        presencas.forEach(presenca => {
            initial[presenca.aluno_id] = {
                presente: presenca.presente,
                is_substituto: presenca.is_substituto,
                substituto_de_time_id: presenca.substituto_de_time_id ?? null,
            };
        });

        return initial;
    });

    const [dialogOpen, setDialogOpen] = useState(false);
    const [selectedSubstituto, setSelectedSubstituto] = useState<number | null>(null);
    const [substitutoTimeId, setSubstitutoTimeId] = useState<number | null>(null);
    const [processing, setProcessing] = useState(false);
    const [confirmRemoveOpen, setConfirmRemoveOpen] = useState(false);
    const [alunoToRemove, setAlunoToRemove] = useState<number | null>(null);

    const isColetivo = jogo.time1 != null && jogo.time2 != null;

    const togglePresenca = (alunoId: number) => {
        setAttendance(prev => ({
            ...prev,
            [alunoId]: {
                ...prev[alunoId],
                presente: !prev[alunoId]?.presente,
            },
        }));
    };

    const addSubstituto = () => {
        if (!selectedSubstituto) return;
        if (isColetivo && !substitutoTimeId) return;

        setAttendance(prev => ({
            ...prev,
            [selectedSubstituto]: {
                presente: true,
                is_substituto: true,
                substituto_de_time_id: isColetivo ? substitutoTimeId : null,
            },
        }));

        setDialogOpen(false);
        setSelectedSubstituto(null);
        setSubstitutoTimeId(null);
    };

    const openRemoveDialog = (alunoId: number) => {
        setAlunoToRemove(alunoId);
        setConfirmRemoveOpen(true);
    };

    const removeSubstituto = () => {
        if (!alunoToRemove) return;
        
        setAttendance(prev => {
            const newAttendance = { ...prev };
            delete newAttendance[alunoToRemove];
            return newAttendance;
        });
        
        setConfirmRemoveOpen(false);
        setAlunoToRemove(null);
    };

    const cancelRemove = () => {
        setConfirmRemoveOpen(false);
        setAlunoToRemove(null);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setProcessing(true);

        const presencasArray = Object.entries(attendance).map(([aluno_id, data]) => ({
            aluno_id: parseInt(aluno_id),
            presente: data.presente,
            is_substituto: data.is_substituto,
            substituto_de_time_id: data.substituto_de_time_id ?? null,
        }));

        router.post(route('presenca.store', jogo.id), { presencas: presencasArray }, {
            onFinish: () => setProcessing(false),
        });
    };

    // Get all athletes to display (expected + substitutos)
    const allAtletas = [
        ...atletasEsperados,
        ...todosAlunos.filter(aluno =>
            attendance[aluno.id]?.is_substituto &&
            !atletasEsperados.find(a => a.id === aluno.id)
        ),
    ];

    // Filter available substitutos (not already in list)
    const availableSubstitutos = todosAlunos.filter(aluno =>
        !allAtletas.find(a => a.id === aluno.id)
    );

    return (
        <Layout>
            <Head title="Presença" />

            <div className="space-y-6">
                <h1 className="text-3xl font-bold">Controle de Presença</h1>

                <Card>
                    <CardHeader>
                        <CardTitle>Informações do Jogo</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex gap-2 mb-4">
                            <Badge>{jogo.categoria.name}</Badge>
                            <Badge variant="outline">{jogo.esporte.name}</Badge>
                        </div>
                        <div className="space-y-2">
                            <p><strong>Data:</strong> {new Date(jogo.data).toLocaleDateString('pt-BR')}</p>
                            <p><strong>Hora:</strong> {jogo.hora}</p>
                            <p><strong>Local:</strong> {jogo.local}</p>
                            {jogo.time1 && jogo.time2 && (
                                <p><strong>Times:</strong> {jogo.time1.name} {jogo.time1.period} vs {jogo.time2.name} {jogo.time2.period}</p>
                            )}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div className="flex justify-between items-center">
                            <CardTitle>Lista de Presença</CardTitle>
                            <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                                <DialogTrigger>
                                    <Button variant="outline" size="sm">
                                        Adicionar Substituto
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>Adicionar Substituto</DialogTitle>
                                    </DialogHeader>
                                    <div className="space-y-4">
                                        <div>
                                            <Label htmlFor="substituto">Selecione o atleta</Label>
                                            <select
                                                id="substituto"
                                                value={selectedSubstituto ?? ''}
                                                onChange={(e) => setSelectedSubstituto(parseInt(e.target.value))}
                                                className="w-full border rounded px-3 py-2 mt-1"
                                            >
                                                <option value="">Selecione...</option>
                                                {availableSubstitutos.map(aluno => (
                                                    <option key={aluno.id} value={aluno.id}>
                                                        {aluno.name} ({aluno.turma.name} {aluno.turma.period})
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                        {isColetivo && (
                                            <div>
                                                <Label htmlFor="substituto-time">Jogará pelo time</Label>
                                                <select
                                                    id="substituto-time"
                                                    value={substitutoTimeId ?? ''}
                                                    onChange={(e) => setSubstitutoTimeId(parseInt(e.target.value))}
                                                    className="w-full border rounded px-3 py-2 mt-1"
                                                >
                                                    <option value="">Selecione o time...</option>
                                                    <option value={jogo.time1!.id}>{jogo.time1!.name} ({jogo.time1!.period})</option>
                                                    <option value={jogo.time2!.id}>{jogo.time2!.name} ({jogo.time2!.period})</option>
                                                </select>
                                            </div>
                                        )}
                                        <Button
                                            onClick={addSubstituto}
                                            disabled={!selectedSubstituto || (isColetivo && !substitutoTimeId)}
                                        >
                                            Adicionar
                                        </Button>
                                    </div>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="space-y-2">
                                {allAtletas.map(atleta => {
                                    const presente = attendance[atleta.id]?.presente ?? true;
                                    const isSubstituto = attendance[atleta.id]?.is_substituto ?? false;

                                    return (
                                        <div key={atleta.id} className="flex items-center justify-between p-3 border rounded">
                                            <div className="flex items-center gap-2">
                                                <span className="font-medium">{atleta.name}</span>
                                                <span className="text-sm text-gray-600">
                                                    ({atleta.turma.name} {atleta.turma.period})
                                                </span>
                                                {isSubstituto && (
                                                    <>
                                                        <Badge variant="outline" className="text-xs">Substituto</Badge>
                                                        <button
                                                            type="button"
                                                            onClick={() => openRemoveDialog(atleta.id)}
                                                            className="text-red-600 hover:text-red-800 p-1"
                                                            title="Remover substituto"
                                                        >
                                                            <X className="h-4 w-4" />
                                                        </button>
                                                    </>
                                                )}
                                            </div>
                                            <Button
                                                type="button"
                                                onClick={() => togglePresenca(atleta.id)}
                                                className={cn(
                                                    "min-w-[100px]",
                                                    presente ? "bg-green-600 hover:bg-green-700" : "bg-gray-400 hover:bg-gray-500"
                                                )}
                                            >
                                                {presente ? "Presente" : "Ausente"}
                                            </Button>
                                        </div>
                                    );
                                })}
                            </div>

                            <Button type="submit" disabled={processing}>
                                Salvar Presença
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <ConfirmDialog
                open={confirmRemoveOpen}
                title="Remover Substituto?"
                message="Tem certeza que deseja remover este substituto da lista de presença?"
                onConfirm={removeSubstituto}
                onCancel={cancelRemove}
                variant="danger"
                confirmText="Remover"
            />
        </Layout>
    );
}
