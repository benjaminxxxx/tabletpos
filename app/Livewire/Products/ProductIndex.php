<?php
// app/Livewire/Products/ProductIndex.php

namespace App\Livewire\Products;

use App\Concerns\HasActiveAccount;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Services\ImageProcessor;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;

class ProductIndex extends Component
{
    use HasActiveAccount, WithFileUploads, WithPagination;

    // Filtros
    public string $search = '';
    public string $status = '';
    public string $gender = '';
    public string $productType = '';
    public string $color = '';
    public int $categoryId = 0;
    public string $sortBy = 'created_at';
    public string $sortDir = 'desc';

    // Panel detalle / galería
    public ?int $selectedProductId = null;
    public bool $showDetail = false;
    public bool $showUploadModal = false;

    // Upload form
    public string $uploadColor = '';
    public string $uploadBrand = '';
    public string $uploadMaterial = '';
    public string $uploadGender = '';
    public int $uploadCategory = 0;
    #[Validate('image|max:10240|mimes:jpg,jpeg,png,webp')]
    public $uploadPhoto = null;


    public function mount(): void
    {
        $this->bootActiveAccount();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingStatus(): void
    {
        $this->resetPage();
    }
    public function updatingGender(): void
    {
        $this->resetPage();
    }
    public function updatingColor(): void
    {
        $this->resetPage();
    }
    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'desc';
        }
    }

    public function openDetail(int $productId): void
    {
        $this->selectedProductId = $productId;
        $this->showDetail = true;
    }

    public function closeDetail(): void
    {
        $this->showDetail = false;
        $this->selectedProductId = null;
    }

    public function openUpload(int $productId): void
    {
        $product = $this->getSelectedProduct($productId);
        if (!$product)
            return;

        // Precargar atributos del producto en el form de upload
        $this->uploadColor = $product->color ?? '';
        $this->uploadBrand = $product->brand ?? '';
        $this->uploadMaterial = $product->material ?? '';
        $this->uploadGender = $product->gender ?? '';
        $this->uploadCategory = $product->category_id ?? 0;
        $this->selectedProductId = $productId;
        $this->showUploadModal = true;
    }

    public function approveMedia(int $mediaId): void
    {
        abort_unless(Gate::allows('manage-account-users'), 403);

        $media = Media::where('account_id', $this->activeAccount->id)->findOrFail($mediaId);
        $media->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Flux::toast(variant: 'success', text: 'Imagen aprobada.');
    }

    public function rejectMedia(int $mediaId): void
    {
        abort_unless(Gate::allows('manage-account-users'), 403);

        $media = Media::where('account_id', $this->activeAccount->id)->findOrFail($mediaId);
        $media->delete();

        Flux::toast(variant: 'warning', text: 'Imagen rechazada y eliminada.');
    }

    #[Computed]
    public function selectedProduct(): ?Product
    {
        if (!$this->selectedProductId)
            return null;

        return Product::with(['category', 'media'])
            ->where('account_id', $this->activeAccount->id)
            ->find($this->selectedProductId);
    }

    #[Computed]
    public function selectedProductMedia()
    {
        if (!$this->selectedProduct)
            return collect();
        return $this->selectedProduct->all_visible_media;
    }

    private function getSelectedProduct(?int $id): ?Product
    {
        if (!$id)
            return null;
        return Product::where('account_id', $this->activeAccount->id)->find($id);
    }

    public function updatedUploadPhoto(): void
    {
        $this->validate(['uploadPhoto' => 'image|max:10240']);
    }

    public function saveUploadedPhoto(): void
    {
        $this->bootActiveAccount();
        $this->validate(['uploadPhoto' => 'required|image|max:51200|mimes:jpg,jpeg,png,webp']);

        $product = $this->getSelectedProduct($this->selectedProductId);
        $isManager = Gate::allows('manage-account-users');
        $folder = "accounts/{$this->activeAccount->id}/media";

        $processor = app(ImageProcessor::class);

        if ($this->uploadPhoto->getMimeType() === 'image/gif') {
            // GIFs — guardar sin redimensionar (pueden ser animados)
            $path = $this->uploadPhoto->store($folder, 'public');
            $paths = ['path_thumb' => $path, 'path_full' => $path, 'mime_type' => 'image/gif'];
        } else {
            $paths = $processor->process($this->uploadPhoto, $folder);
        }

        $media = Media::create([
            'account_id' => $this->activeAccount->id,
            'uploaded_by' => auth()->id(),
            'path' => $paths['path_full'],       // path principal = full
            'path_thumb' => $paths['path_thumb'],
            'path_full' => $paths['path_full'],
            'disk' => 'public',
            'type' => 'photo',
            'mime_type' => $paths['mime_type'],
            'size' => $this->uploadPhoto->getSize(),
            'original_name' => $this->uploadPhoto->getClientOriginalName(),
            'category_id' => $product?->category_id,
            'color' => $product?->color,
            'brand' => $product?->brand,
            'material' => $product?->material,
            'gender' => $product?->gender,
            'status' => $isManager ? 'approved' : 'pending',
            'approved_by' => $isManager ? auth()->id() : null,
            'approved_at' => $isManager ? now() : null,
        ]);

        if ($product) {
            $maxOrder = $product->media()->max('sort_order') ?? -1;
            $product->media()->attach($media->id, ['sort_order' => $maxOrder + 1]);
        }

        $this->uploadPhoto = null;
        $this->showUploadModal = false;

        Flux::toast(
            variant: 'success',
            text: $isManager ? 'Imagen agregada.' : 'Imagen enviada — pendiente de aprobación.'
        );
    }

    public function render()
    {
        $products = Product::with(['category', 'media'])
            ->where('account_id', $this->activeAccount->id)
            ->when(
                $this->search,
                fn($q) =>
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('public_code', 'like', "%{$this->search}%")
                        ->orWhere('brand', 'like', "%{$this->search}%")
                        ->orWhere('color', 'like', "%{$this->search}%");
                })
            )
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->gender, fn($q) => $q->where('gender', $this->gender))
            ->when($this->productType, fn($q) => $q->where('product_type', $this->productType))
            ->when($this->color, fn($q) => $q->where('color', 'like', "%{$this->color}%"))
            ->when($this->categoryId, fn($q) => $q->where('category_id', $this->categoryId))
            ->when($this->sortBy === 'rent_count', fn($q) => $q->orderBy('rent_count', $this->sortDir))
            ->when($this->sortBy === 'sale_count', fn($q) => $q->orderBy('sale_count', $this->sortDir))
            ->when(
                !in_array($this->sortBy, ['rent_count', 'sale_count']),
                fn($q) => $q->orderBy($this->sortBy, $this->sortDir)
            )
            ->paginate(24); // 24 = 4 col × 6 filas en tablet

        $categories = Category::whereNotNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        $statusCounts = Product::where('account_id', $this->activeAccount->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('livewire.products.product-index', compact(
            'products',
            'categories',
            'statusCounts'
        ));
    }
}