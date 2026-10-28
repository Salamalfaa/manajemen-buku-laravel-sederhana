<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BukuController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'year' => ['nullable', 'integer', 'digits:4'],
            'sort' => ['nullable', 'in:newest,oldest,title,author,year'],
        ]);
        $search = trim($filters['search'] ?? '');
        $year = $filters['year'] ?? null;
        $sort = $filters['sort'] ?? 'newest';
        $sortOptions = [
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'title' => ['judul', 'asc'],
            'author' => ['penulis', 'asc'],
            'year' => ['tahun_terbit', 'desc'],
        ];

        $bukus = Buku::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('penerbit', 'like', "%{$search}%");
                });
            })
            ->when($year, fn ($query) => $query->where('tahun_terbit', $year))
            ->orderBy(...$sortOptions[$sort])
            ->paginate(9)
            ->withQueryString();

        return view('buku.index', [
            'bukus' => $bukus,
            'search' => $search,
            'year' => $year,
            'sort' => $sort,
            'totalBuku' => Buku::count(),
            'totalHasil' => $bukus->total(),
            'totalPenulis' => Buku::query()->distinct('penulis')->count('penulis'),
            'bukuTerbaru' => Buku::max('tahun_terbit'),
            'tahunTerbit' => Buku::query()->select('tahun_terbit')->distinct()->orderByDesc('tahun_terbit')->pluck('tahun_terbit'),
        ]);
    }

    public function create(): View
    {
        return view('buku.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Buku::create($this->validatedData($request));

        return redirect()->route('buku.index')->with('success', 'Buku berhasil ditambahkan ke koleksi.');
    }

    public function edit(Buku $buku): View
    {
        return view('buku.edit', compact('buku'));
    }

    public function update(Request $request, Buku $buku): RedirectResponse
    {
        $buku->update($this->validatedData($request));

        return redirect()->route('buku.index')->with('success', 'Informasi buku berhasil diperbarui.');
    }

    public function destroy(Buku $buku): RedirectResponse
    {
        $buku->delete();

        return redirect()->route('buku.index')->with('success', 'Buku berhasil dihapus dari koleksi.');
    }

    /**
     * @return array{judul: string, penulis: string, penerbit: string, tahun_terbit: int}
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'penulis' => ['required', 'string', 'max:255'],
            'penerbit' => ['required', 'string', 'max:255'],
            'tahun_terbit' => ['required', 'integer', 'min:1000', 'max:'.(now()->year + 1)],
        ]);
    }
}
