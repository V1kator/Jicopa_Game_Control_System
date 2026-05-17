import { useState, useCallback } from 'react';
import { useForm } from '@inertiajs/react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

interface AvaliacaoConfig {
    id: number;
    num_jurados: number;
    nota_min: number;
    nota_max: number;
    pontos_bonus_melhor: number;
}

interface Categoria {
    id: number;
    name: string;
}

interface Turma {
    id: number;
    name: string;
    period: string;
}

// notas[turma_id][jurado_num] = nota (string for input binding)
type NotasMap = Record<number, Record<number, string>>;

interface Props {
    config: AvaliacaoConfig;
    categorias: Categoria[];
    turmas: Turma[];
    notas: Record<number, Record<number, string>>;
    categoriaId: number | null;
}

export default function AvaliacaoNotas({ config, categorias, turmas, notas: initialNotas, categoriaId }: Props) {
    // Local state for grid inputs (real-time validation without submit)
    const [notasState, setNotasState] = useState<NotasMap>(() => {
        const map: NotasMap = {};
        for (const turmaId in initialNotas) {
            map[Number(turmaId)] = {};
            for (const juradoNum in initialNotas[turmaId]) {
                map[Number(turmaId)][Number(juradoNum)] = String(initialNotas[turmaId][juradoNum]);
            }
        }
        return map;
    });

    const [saving, setSaving] = useState(false);

    const jurados = Array.from({ length: config.num_jurados }, (_, i) => i + 1);

    const isOutOfRange = useCallback((value: string): boolean => {
        if (value === '' || value === null || value === undefined) return false;
        const num = parseFloat(value);
        return isNaN(num) || num < config.nota_min || num > config.nota_max;
    }, [config.nota_min, config.nota_max]);

    function handleNotaChange(turmaId: number, juradoNum: number, value: string) {
        setNotasState((prev) => ({
            ...prev,
            [turmaId]: {
                ...(prev[turmaId] ?? {}),
                [juradoNum]: value,
            },
        }));
    }

    function calcularTotal(turmaId: number): string {
        const turmaNotas = notasState[turmaId] ?? {};
        let total = 0;
        let hasAny = false;

        for (const juradoNum of jurados) {
            const val = turmaNotas[juradoNum];
            if (val !== undefined && val !== '') {
                const num = parseFloat(val);
                if (!isNaN(num)) {
                    total += num;
                    hasAny = true;
                }
            }
        }

        return hasAny ? total.toFixed(1) : '—';
    }

    function handleCategoriaChange(e: React.ChangeEvent<HTMLSelectElement>) {
        const value = e.target.value;
        router.get(route('admin.avaliacao-notas.index'), { categoria_id: value || undefined }, { preserveState: false });
    }

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();

        if (!categoriaId) return;

        // Build flat array of notas for submission
        const notasArray: { turma_id: number; jurado_num: number; nota: string }[] = [];

        for (const turma of turmas) {
            for (const juradoNum of jurados) {
                const val = notasState[turma.id]?.[juradoNum];
                if (val !== undefined && val !== '') {
                    notasArray.push({
                        turma_id: turma.id,
                        jurado_num: juradoNum,
                        nota: val,
                    });
                }
            }
        }

        setSaving(true);
        router.post(
            route('admin.avaliacao-notas.store'),
            { categoria_id: categoriaId, notas: notasArray },
            {
                onFinish: () => setSaving(false),
            }
        );
    }

    const hasValidationErrors = turmas.some((turma) =>
        jurados.some((juradoNum) => {
            const val = notasState[turma.id]?.[juradoNum];
            return val !== undefined && val !== '' && isOutOfRange(val);
        })
    );

    return (
        <AdminLayout title="Registro de Notas">
            <Head title="Registro de Notas de Avaliação" />

            <div className="space-y-6">
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Registro de Notas — Bandeira/Grito</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            Intervalo válido: {config.nota_min} a {config.nota_max} | {config.num_jurados} jurado(s)
                        </p>
                    </div>
                    <Link
                        href={route('admin.avaliacao-config.index')}
                        className="text-sm text-gray-500 hover:text-gray-700"
                    >
                        Configurações
                    </Link>
                </div>

                {/* Categoria filter */}
                <div className="bg-surface rounded-xl shadow-sm p-4">
                    <label htmlFor="categoria_id" className="block text-sm font-medium text-gray-700 mb-1">
                        Categoria
                    </label>
                    <select
                        id="categoria_id"
                        value={categoriaId ?? ''}
                        onChange={handleCategoriaChange}
                        className="w-full max-w-xs rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                    >
                        <option value="">Selecione uma categoria...</option>
                        {categorias.map((cat) => (
                            <option key={cat.id} value={cat.id}>
                                {cat.name}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Score grid */}
                {categoriaId && (
                    <form onSubmit={handleSubmit}>
                        <div className="bg-surface rounded-xl shadow-sm overflow-hidden">
                            {turmas.length === 0 ? (
                                <div className="p-6 text-center text-gray-500 text-sm">
                                    Nenhuma turma vinculada a esta categoria.
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead className="bg-gray-50 border-b border-gray-200">
                                            <tr>
                                                <th className="text-left px-4 py-3 font-medium text-gray-700 whitespace-nowrap">
                                                    Turma
                                                </th>
                                                {jurados.map((juradoNum) => (
                                                    <th
                                                        key={juradoNum}
                                                        className="text-center px-4 py-3 font-medium text-gray-700 whitespace-nowrap"
                                                    >
                                                        Jurado {juradoNum}
                                                    </th>
                                                ))}
                                                <th className="text-center px-4 py-3 font-medium text-gray-700 whitespace-nowrap">
                                                    Total
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-100">
                                            {turmas.map((turma) => (
                                                <tr key={turma.id} className="hover:bg-gray-50">
                                                    <td className="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                                        {turma.name}
                                                        <span className="ml-1 text-xs text-gray-400">
                                                            ({turma.period})
                                                        </span>
                                                    </td>
                                                    {jurados.map((juradoNum) => {
                                                        const val = notasState[turma.id]?.[juradoNum] ?? '';
                                                        const invalid = val !== '' && isOutOfRange(val);
                                                        return (
                                                            <td key={juradoNum} className="px-2 py-2 text-center">
                                                                <input
                                                                    type="number"
                                                                    step="0.1"
                                                                    min={config.nota_min}
                                                                    max={config.nota_max}
                                                                    value={val}
                                                                    onChange={(e) =>
                                                                        handleNotaChange(turma.id, juradoNum, e.target.value)
                                                                    }
                                                                    placeholder={`${config.nota_min}–${config.nota_max}`}
                                                                    className={[
                                                                        'w-24 rounded-md border px-2 py-1 text-sm text-center focus:outline-none focus:ring-2',
                                                                        invalid
                                                                            ? 'border-red-500 bg-red-50 focus:ring-red-300'
                                                                            : 'border-gray-300 focus:ring-primary/40',
                                                                    ].join(' ')}
                                                                />
                                                            </td>
                                                        );
                                                    })}
                                                    <td className="px-4 py-3 text-center font-semibold text-gray-900 whitespace-nowrap">
                                                        {calcularTotal(turma.id)}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>

                        {turmas.length > 0 && (
                            <div className="mt-4 flex items-center gap-4">
                                <button
                                    type="submit"
                                    disabled={saving || hasValidationErrors}
                                    className="bg-primary text-primary-foreground px-5 py-2 rounded-md text-sm font-medium hover:opacity-90 transition-opacity disabled:opacity-50"
                                >
                                    {saving ? 'Salvando...' : 'Salvar Notas'}
                                </button>
                                {hasValidationErrors && (
                                    <p className="text-sm text-red-600">
                                        Corrija as notas fora do intervalo ({config.nota_min}–{config.nota_max}) antes de salvar.
                                    </p>
                                )}
                            </div>
                        )}
                    </form>
                )}
            </div>
        </AdminLayout>
    );
}
