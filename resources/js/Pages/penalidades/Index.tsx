import { Head, useForm, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import ProfessorLayout from '@/Layouts/ProfessorLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/Components/ui/dialog';
import { Label } from '@/Components/ui/label';
import { Input } from '@/Components/ui/input';
import { useState } from 'react';
import { PageProps } from '@/types';
import ConfirmDialog from '@/Components/shared/ConfirmDialog';

interface Turma {
    id: number;
    name: string;
    period: string;
}

interface Aluno {
    id: number;
    name: string;
    turma: { name: string; period: string };
}

interface Penalidade {
    id: number;
    tipo: 'turma' | 'aluno';
    turma?: Turma;
    aluno?: Aluno;
    motivo: string;
    pontos: number;
    registrado_por: { name: string };
    created_at: string;
}

interface Props extends PageProps {
    penalidades: Penalidade[];
    turmas: Turma[];
    alunos: Aluno[];
}

export default function Index({ auth, penalidades, turmas, alunos }: Props) {
    const Layout = auth.user.roles.includes('admin') ? AdminLayout : ProfessorLayout;

    const [dialogOpen, setDialogOpen] = useState(false);
    const [editingPenalidade, setEditingPenalidade] = useState<Penalidade | null>(null);
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        tipo: 'turma' as 'turma' | 'aluno',
        turma_id: null as number | null,
        aluno_id: null as number | null,
        motivo: '',
        pontos: 1,
    });

    const openCreateDialog = () => {
        reset();
        setEditingPenalidade(null);
        setDialogOpen(true);
    };

    const openEditDialog = (penalidade: Penalidade) => {
        setData({
            tipo: penalidade.tipo,
            turma_id: penalidade.turma?.id ?? null,
            aluno_id: penalidade.aluno?.id ?? null,
            motivo: penalidade.motivo,
            pontos: penalidade.pontos,
        });
        setEditingPenalidade(penalidade);
        setDialogOpen(true);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (editingPenalidade) {
            put(route('penalidades.update', editingPenalidade.id), {
                onSuccess: () => {
                    setDialogOpen(false);
                    reset();
                },
            });
        } else {
            post(route('penalidades.store'), {
                onSuccess: () => {
                    setDialogOpen(false);
                    reset();
                },
            });
        }
    };

    const handleDelete = (id: number) => {
        router.delete(route('penalidades.destroy', id), {
            onSuccess: () => setConfirmDelete(null),
        });
    };

    const formatDate = (dateStr: string) => {
        const date = new Date(dateStr);
        return date.toLocaleDateString('pt-BR') + ' às ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    };

    return (
        <Layout>
            <Head title="Penalidades" />

            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <h1 className="text-3xl font-bold">Penalidades</h1>
                    <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                        <DialogTrigger>
                            <Button onClick={openCreateDialog}>Registrar Penalidade</Button>
                        </DialogTrigger>
                        <DialogContent className="max-w-2xl">
                            <DialogHeader>
                                <DialogTitle>
                                    {editingPenalidade ? 'Editar Penalidade' : 'Registrar Penalidade'}
                                </DialogTitle>
                            </DialogHeader>
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div>
                                    <Label>Tipo</Label>
                                    <div className="flex gap-4 mt-2">
                                        <label className="flex items-center space-x-2 cursor-pointer">
                                            <input
                                                type="radio"
                                                name="tipo"
                                                value="turma"
                                                checked={data.tipo === 'turma'}
                                                onChange={() =>
                                                    setData({
                                                        ...data,
                                                        tipo: 'turma',
                                                        turma_id: null,
                                                        aluno_id: null,
                                                    })
                                                }
                                                className="accent-primary"
                                            />
                                            <span>Turma</span>
                                        </label>
                                        <label className="flex items-center space-x-2 cursor-pointer">
                                            <input
                                                type="radio"
                                                name="tipo"
                                                value="aluno"
                                                checked={data.tipo === 'aluno'}
                                                onChange={() =>
                                                    setData({
                                                        ...data,
                                                        tipo: 'aluno',
                                                        turma_id: null,
                                                        aluno_id: null,
                                                    })
                                                }
                                                className="accent-primary"
                                            />
                                            <span>Aluno</span>
                                        </label>
                                    </div>
                                    {errors.tipo && (
                                        <p className="text-sm text-red-600 mt-1">{errors.tipo}</p>
                                    )}
                                </div>

                                {data.tipo === 'turma' ? (
                                    <div>
                                        <Label htmlFor="turma_id">Turma</Label>
                                        <select
                                            id="turma_id"
                                            value={data.turma_id ?? ''}
                                            onChange={(e) =>
                                                setData('turma_id', parseInt(e.target.value) || null)
                                            }
                                            className="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm"
                                        >
                                            <option value="">Selecione...</option>
                                            {turmas.map((turma) => (
                                                <option key={turma.id} value={turma.id}>
                                                    {turma.name} {turma.period}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.turma_id && (
                                            <p className="text-sm text-red-600 mt-1">{errors.turma_id}</p>
                                        )}
                                    </div>
                                ) : (
                                    <div>
                                        <Label htmlFor="aluno_id">Aluno</Label>
                                        <select
                                            id="aluno_id"
                                            value={data.aluno_id ?? ''}
                                            onChange={(e) =>
                                                setData('aluno_id', parseInt(e.target.value) || null)
                                            }
                                            className="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm"
                                        >
                                            <option value="">Selecione...</option>
                                            {alunos.map((aluno) => (
                                                <option key={aluno.id} value={aluno.id}>
                                                    {aluno.name} ({aluno.turma.name} {aluno.turma.period})
                                                </option>
                                            ))}
                                        </select>
                                        {errors.aluno_id && (
                                            <p className="text-sm text-red-600 mt-1">{errors.aluno_id}</p>
                                        )}
                                    </div>
                                )}

                                <div>
                                    <Label htmlFor="motivo">Motivo</Label>
                                    <textarea
                                        id="motivo"
                                        value={data.motivo}
                                        onChange={(e) => setData('motivo', e.target.value)}
                                        rows={3}
                                        maxLength={1000}
                                        className="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                    />
                                    {errors.motivo && (
                                        <p className="text-sm text-red-600 mt-1">{errors.motivo}</p>
                                    )}
                                </div>

                                <div>
                                    <Label htmlFor="pontos">Pontos a descontar</Label>
                                    <Input
                                        id="pontos"
                                        type="number"
                                        min="1"
                                        value={data.pontos}
                                        onChange={(e) => setData('pontos', parseInt(e.target.value) || 1)}
                                        className="mt-1"
                                    />
                                    {errors.pontos && (
                                        <p className="text-sm text-red-600 mt-1">{errors.pontos}</p>
                                    )}
                                </div>

                                <div className="flex gap-2">
                                    <Button type="submit" disabled={processing}>
                                        {editingPenalidade ? 'Atualizar' : 'Registrar'}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => setDialogOpen(false)}
                                    >
                                        Cancelar
                                    </Button>
                                </div>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Histórico de Penalidades</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2">
                            {penalidades.length === 0 ? (
                                <p className="text-gray-600 text-center py-8">
                                    Nenhuma penalidade registrada.
                                </p>
                            ) : (
                                penalidades.map((penalidade) => (
                                    <div
                                        key={penalidade.id}
                                        className="flex items-center justify-between p-4 border border-gray-100 rounded-lg hover:bg-gray-50"
                                    >
                                        <div className="flex-1 space-y-1">
                                            <div className="flex items-center gap-2 flex-wrap">
                                                <Badge
                                                    variant={penalidade.tipo === 'turma' ? 'default' : 'secondary'}
                                                >
                                                    {penalidade.tipo === 'turma' ? 'Turma' : 'Aluno'}
                                                </Badge>
                                                <span className="font-medium text-gray-900">
                                                    {penalidade.tipo === 'turma' && penalidade.turma
                                                        ? `${penalidade.turma.name} ${penalidade.turma.period}`
                                                        : penalidade.aluno
                                                        ? `${penalidade.aluno.name} (${penalidade.aluno.turma.name} ${penalidade.aluno.turma.period})`
                                                        : 'N/A'}
                                                </span>
                                                <Badge variant="destructive">
                                                    -{penalidade.pontos} pts
                                                </Badge>
                                            </div>
                                            <p className="text-sm text-gray-600">{penalidade.motivo}</p>
                                            <p className="text-xs text-gray-500">
                                                Registrado por {penalidade.registrado_por?.name ?? '—'} em{' '}
                                                {formatDate(penalidade.created_at)}
                                            </p>
                                        </div>
                                        <div className="flex gap-2 ml-4 shrink-0">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => openEditDialog(penalidade)}
                                            >
                                                Editar
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                onClick={() => setConfirmDelete(penalidade.id)}
                                            >
                                                Remover
                                            </Button>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <ConfirmDialog
                open={confirmDelete !== null}
                title="Remover Penalidade"
                message="Tem certeza que deseja remover esta penalidade?"
                onConfirm={() => confirmDelete && handleDelete(confirmDelete)}
                onCancel={() => setConfirmDelete(null)}
                variant="danger"
                confirmText="Remover"
            />
        </Layout>
    );
}
