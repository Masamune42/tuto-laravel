<form action="" method="POST" class="container mt-5" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label for="image" class="form-label">Titre</label>
        <input type="file" name="image" id="image" class="form-control">
        @error('image')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-3">
        <label for="title" class="form-label">Titre</label>
        <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $post->title) }}">
        @error('title')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-3">
        <label for="slug" class="form-label">Slug</label>
        <textarea name="slug" id="slug" class="form-control">{{ old('slug', $post->slug) }}</textarea>
        @error('slug')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-3">
        <label for="content" class="form-label">Contenu</label>
        <textarea name="content" id="content" class="form-control">{{ old('content', $post->content) }}</textarea>
        @error('content')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-3">
        <label for="category" class="form-label">Catégorie</label>
        <select name="category_id" id="category" class="form-select">
            <option value="">Sélectionner une catégorie</option>
            @foreach ($categories as $category)
                <option @selected(old('category', $post->category_id) == $category->id) value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-3">
        @php
            $tagsIds = $post->tags()->pluck('id');
        @endphp
        <label for="tag" class="form-label">Tags</label>
        <select name="tags[]" id="tag" class="form-select" multiple>
            @foreach ($tags as $tag)
                <option @selected($tagsIds->contains($tag->id)) value="{{ $tag->id }}">{{ $tag->name }}</option>
            @endforeach
        </select>
        @error('tags')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <button type="submit" class="btn btn-primary">
        @if ($post->id)
            Modifier
        @else
            Enregistrer
        @endif
    </button>
</form>
