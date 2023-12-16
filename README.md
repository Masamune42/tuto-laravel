# Les bases à connaitre

## Le Routing
A configurer dans **routes\web.php**
```php
// URL get '/' pour accéder à la vue 'welcome'
Route::get('/', function () {
    return view('welcome');
});

// URL get '/blog' pour retourner un json avec 'article' qui contiendra l'élément de la requête 'name' sinon 'john' par défaut
// Nom : blog.index
Route::get('/blog', function(Request $request) {
    return ['article' => $request->input('name', 'john')];
})->name('blog.index');

// URL get qui va contenir un slug et un id que l'on défini dans la fonction
// where => Défini le format attendu pour id (numérique) et slug (chaines de caractères en acceptant que des tirets)
// Nom : blog.show
Route::get('/blog/{slug}-{id}', function(string $slug, string $id) {
    return [
        "slug" => $slug,
        "id" => $id,
    ];
})->where([
    'id' => '[0-9]+',
    'slug' => '[a-z0-9\-]+',
])->name('blog.show');

// On peut regrouper les routes de cette façon
// toutes les routes préfixer par '/blog' et avec un nom qui commencent par 'blog.'
Route::prefix('/blog')->name('blog.')->group(function() {
    Route::get('/', function(Request $request) {
        return ['article' => $request->input('name', 'john')];
    })->name('index');
    
    Route::get('/{slug}-{id}', function(string $slug, string $id) {
        return [
            "slug" => $slug,
            "id" => $id,
        ];
    })->where([
        'id' => '[0-9]+',
        'slug' => '[a-z0-9\-]+',
    ])->name('show');
});
```
Pour accéder à toutes les routes
```
php artisan route:list
```

## ORM Eloquent
Créer une migration
```
php artisan make:migration CreatePostTable
```
On configure la migration
```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->longText('content');
    $table->timestamps();
});
```
On envoie en BDD
```
php artisan migrate
```

On crée un modèle (avec -m si l'on veut créer la migration en même temps)
```
php artisan make:model Post
php artisan make:model Post -m
```

Exemple de création d'un article dans **web.php**
```php
$post = new Post();
$post->title = "Mon premier article";
$post->slug = "mon-premier-article";
$post->content = "Contenu";
$post->save();

return $post;
```

Exemple de visualisation dans **web.php**
```php
// Retourne tous les articles
return Post::all();

// Retourne tous les articles avec uniquement les champs id et title
return Post::all(['id', 'title']);

// On renvoie un objet de type Collection qui contient les items de type Post
$posts = Post::all(['id', 'title']);
dd($posts);

// On accède au title du 1er élément
dd($posts[0]->title);

// On récupère le post avec l'id 2, renvoie null si la donnée n'a pas été trouvée
$posts = Post::find(2);

// Renvoie une page d'erreur si la donnée n'a pas été trouvée
$posts = Post::findOrFail(3);

// Pagination : 1 élément par page
// Avec un return, est converti et affiché : affichage toutes les informations concernant la pagination
$posts = Post::paginate(1);
// On ne retourne que l'id et le title
$posts = Post::paginate(1, ['id', 'title']);

// QueryBuilder
// On récupère tous les éléments avec un id > 1
$posts = Post::where('id', '>', '0')->get();
// On limite à 1 résultat
$posts = Post::where('id', '>', '1')->limit(1)->get();

// Modifier un élément
$post = Post::find(1);
$post->title = 'Nouveau titre';
$post->save();

// Supprimer un élément
$post = Post::find(1);
$post->delete();


// Création via un tableau
// Au préalable ajouter le code dans Post.php avec tous les champs que l'on peut créer
// Option inverse avec $guarded
protected $fillable = [
    'title',
    'slug',
    'content',
];
// On peut ainsi créer de cette façon
$post = Post::create([
    'title' => 'Mon nouveau titre',
    'slug' => 'nouveau-titre',
    'content' => 'Contenu',
]);

// Aussi possible avec where sur update / delete
$post = Post::where('id', '>', 1)->update([
    'title' => 'Mon nouveau titre',
    'slug' => 'nouveau-titre',
    'content' => 'Contenu',
]);

```

## Controllers
```
php artisan make:controller PostController
```
On récupère les actions à exécuter dans les pages dans le controller qu'on appelle dans **web.php**
```php
// web.php
Route::prefix('/blog')->name('blog.')->group(function() {

    Route::get('/', [BlogController::class, 'index'])->name('index');
    
    Route::get('/{slug}-{id}', [BlogController::class, 'show'])->where([
        'id' => '[0-9]+',
        'slug' => '[a-z0-9\-]+',
    ])->name('show');
});

// BlogController.php
class BlogController extends Controller
{
    public function index(): Paginator
    {
        return Post::paginate(25);
    }

    public function show(string $slug, string $id): RedirectResponse | Post
    {
        $post = Post::findOrFail($id);
        if($post->slug !== $slug) {
            return to_route('blog.show', ['slug' => $post->slug, 'id' => $post->id]);
        }
        return $post;
    }
}
```
On peut aussi grouper au niveau du controller
```php
Route::prefix('/blog')->name('blog.')->controller(BlogController::class)->group(function() {

    Route::get('/', 'index')->name('index');
    
    Route::get('/{slug}-{id}', 'show')->where([
        'id' => '[0-9]+',
        'slug' => '[a-z0-9\-]+',
    ])->name('show');
});
```


## Blade
On crée les vues index et show pour les appeler avec des paramètres dans les controller
```php
public function index(): View
{
    return view('blog.index', [
        'posts' => Post::paginate(1)
    ]);
}

public function show(string $slug, string $id): RedirectResponse | View
{
    $post = Post::findOrFail($id);
    if($post->slug !== $slug) {
        return to_route('blog.show', ['slug' => $post->slug, 'id' => $post->id]);
    }
    return view('blog.show', [
        'post' => $post
    ]);
}
```
**base.blade.php** pour créer une base HTML réutilisable pour les autres vues
```php
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body>

    @php
        $routeName = request()->route()->getName();
    @endphp
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Blog</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor01"
                aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarColor01">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a  @class(['nav-link', 'active' =>  Str::startsWith($routeName, 'blog.')]) href="{{ route('blog.index') }}">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Link</a>
                    </li>
                </ul>
            </div>
        </div>

    </nav>

    <div class="container">
        @yield('content')
    </div>
</body>

</html>
```
Comme on utilise Bootstrap 5 on l'indique dans le fichier **app\Providers\AppServiceProvider.php**
```php
public function boot(): void
{
    Paginator::useBootstrapFive();
}
```
**index.blade.php**
```php
@extends('base')

@section('title', 'Accueil du blog')

@section('content')
    <h1>Mon blog</h1>
    
    @foreach ($posts as $post)
        <article>
            <h2>{{ $post->title }}</h2>
            <p>
                {{ $post->content }}
            </p>
            <p>
                <a href="{{ route('blog.show', ['slug' => $post->slug, 'id' => $post->id]) }}" class="btn btn-primary">Lire la suite</a>
            </p>
        </article>
    @endforeach

    {{ $posts->links() }}
@endsection
```
**show.blade.php**
```php
@extends('base')

@section('title', $post->title)

@section('content')
    <article>
        <h2>{{ $post->title }}</h2>
        <p>
            {{ $post->content }}
        </p>
    </article>
@endsection
```

## Validator
Dans le controller
```php
// 1er paramètre : champ reçu de la requête, 2e paramètre : les règles
$validator = Validator::make([
    'title' => '',
    'content' => ''
], [
    'title' => 'required|min:8'
]);


// renvoie true si la validation échoue
$validator->fails();

// renvoie les messages d'erreur
$validator->errors()

// Renvoie les champs validés
// Si aucune donnée validée, renvoie vers la page précédente par défaut
$validator->validated()

// Les règles peuvent être écrites en tableau
$validator = Validator::make([
    'title' => 'a',
    'content' => 'zzaza'
], [
    'title' => ['required', 'min:8', 'regex:pattern']
]);

// title doit être unique dans la table post en ignorant l'entrée avec l'ID 2
$validator = Validator::make([
    'title' => 'a',
    'content' => 'zzaza'
], [
    'title' => [Rule::unique('post')->ignore(2)]
]);
```

On peut créer des requêtes personnalisées pour valider les données
```
php artisan make:request BlogFilterRequest
```

On y place la règle dans une fonction
```php
// Dans BlogFilterRequest
public function rules(): array
{
    return [
        'title' => ['required', 'min:4'],
        'slug' => ['required', 'regex:/^[a-z0-9\-]+$/']
    ];
}

// Dans BlogController
public function index(BlogFilterRequest $request): View
{
    return view('blog.index', [
        'posts' => Post::paginate(1)
    ]);
}

// On peut y définir une fonction appelée avant la validation
// Si slug existe dans la requête on l'utilise, sinon on slugify title dans la requête
protected function  prepareForValidation()
{
    $this->merge([
        'slug' => $this->input('slug') ?: Str::slug($this->input('title'))
    ]);
}
```

## Model Binding
On peut pré-récupérer les informations lorsqu'on a une route spécifique
1. On change le nommage dans l'URL (id > post)
```php
Route::get('/{slug}-{post}', 'show')->where([
    'id' => '[0-9]+',
    'slug' => '[a-z0-9\-]+',
])->name('show');
```
2. On renomme dans le controller et on récupère un objet Post. On peut supprimer le findOrFail.
```php
public function show(string $slug, Post $post): RedirectResponse | View
{
    if($post->slug !== $slug) {
        return to_route('blog.show', ['slug' => $post->slug, 'id' => $post->id]);
    }
    return view('blog.show', [
        'post' => $post
    ]);
}
```
3. On change dans les vues les urls
```php
<a href="{{ route('blog.show', ['slug' => $post->slug, 'post' => $post->id]) }}" class="btn btn-primary">Lire la suite</a>
```
Peut fonctionner aussi avec le slug
```php
// web.php
Route::get('/{post:slug}', 'show')->where([
    'post' => '[a-z0-9\-]+',
])->name('show');
// BlogController.php
public function show(Post $post): RedirectResponse | View
{
    return view('blog.show', [
        'post' => $post
    ]);
}
```

## Debug
On peut se servir d'un outil de debug dans Laravel en installant une librairie
```
composer require barryvdh/laravel-debugbar --dev
```


## Formulaires
Pour gérer les formulaires on en crée sous blade avec un jeton CSRF.
Comme on va l'utiliser pour créer et modifier on le crée dans un fichier **form.blade.php**
```php
<form action="" method="POST">
    @csrf
    <div>
        <input type="text" name="title" value="{{ old('title', $post->title) }}">
        @error("title")
            {{ $message }}
        @enderror
    </div>
        <div>
        <textarea name="slug">{{ old('slug', $post->slug) }}</textarea>
        @error("slug")
            {{ $message }}
        @enderror
    </div>
        <div>
        <textarea name="content">{{ old('content', $post->content) }}</textarea>
        @error("content")
            {{ $message }}
        @enderror
    </div>
    <button type="submit">
        @if ($post->id)
            Modifier
        @else
            Enregistrer
        @endif
    </button>
</form>
```

On gère les routes dans **web.php** et dans le controller
```php
// web.php
Route::get('/new', 'create')->name('create');
Route::post('/new', 'store');
Route::get('/{post}/edit', 'edit')->name('edit');
Route::post('/{post}/edit', 'update');

// BlogController
public function create() : View
{
    $post = new Post();
    return view('blog.create', [
        'post' => $post
    ]);
}

public function store(FormPostRequest $request)
{
    $post = Post::create($request->validated());
    return redirect()->route('blog.show', ['slug' => $post->slug, 'post' => $post->id])->with('success', "L'article a bien été sauvegardé");
}

public function edit(Post $post)
{
    // On crée un article vide car le formulaire attends un objet Post
    return view('blog.edit', [
        'post' => $post
    ]);
}

public function update(Post $post, FormPostRequest $request)
{
    $post->update($request->validated());
    return redirect()->route('blog.show', ['slug' => $post->slug, 'post' => $post->id])->with('success', "L'article a bien été modifié");
}
```
## Relation
On crée Category : Model +  migration
```
php artisan make:model Category -m
```

On spécifie que chaque catégorie n'appartient qu'à un article
```php
// Migration
public function up(): void
{
    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
    Schema::table('posts', function(Blueprint $table) {
        $table->foreignIdFor(Category::class)->nullable()->constrained()->cascadeOnDelete();
    });
}

/**
 * Reverse the migrations.
 */
public function down(): void
{
    Schema::dropIfExists('categories');
    Schema::table('posts', function(Blueprint $table) {
        $table->dropForeignIdFor(Category::class);
    });
}

// Post.php
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

Pour récupérer les informations
```php
// On récupère tous les articles avec une catégorie associée
$posts = Post::with('category')->get();
```

Pour récupérer les articles associés à une catégorie
```php
// Dans Category.php
public function posts() {
    return $this->hasMany(Post::class);
}
```

```php
// On récupère la catégorie avec l'id 1
$category = Category::find(1);
// On récupère les articles lié à la catégorie 1 qui ont un id supérieur à 10
$category->posts()->where('id', '>', '10')->get();

// Pour associer une catégorie à un article
$category = Category::find(1);
$post = Post::find(6);
$post->category()->associate($category);
$post->save();
```
On crée un modèle et une migration pour Tag et on prépare la migration
```php
public function up(): void
{
    Schema::create('tags', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
    Schema::create('post_tag', function(Blueprint $table) {
        $table->foreignIdFor(Post::class)->constrained()->cascadeOnDelete();
        $table->foreignIdFor(Tag::class)->constrained()->cascadeOnDelete();
        $table->primary(['post_id', 'tag_id']);
    });
}

/**
 * Reverse the migrations.
 */
public function down(): void
{
    Schema::dropIfExists('post_tag');
    Schema::dropIfExists('tags');
}

// On prépare le modèle Tag
class Tag extends Model
{
    use HasFactory;

    public function posts() {
        return $this->belongsToMany(Post::class);
    }

    protected $fillable = [
        'name'
    ];
}

// On modifie le modèle Post en créant une fonction
public function tags() {
    return $this->belongsToMany(Tag::class);
}

// On récupère l'article avec l'ID 2 et on lui assigne 2 tags que l'on crée
$post = Post::find(2);
$post->tags()->createMany([[
    'name' => 'Tag 1'
] , [
    'name' => 'Tag 2'
]]);

// On peut attacher et détacher le tag d'un article
$post = Post::find(2);
$post->tags()->detach(2);
$post->tags()->attach(2);
// On peut synchroniser un article avec des tags ou vide
$post->tags()->sync([1, 2]);
// On récupère les articles qui possèdent au moins un tag
Post::has('tags', '>=', 1)->get()
```

On inclut le formulaire suivant dans les pages de création et modification
```php
<form action="" method="POST" class="container mt-5">
    @csrf
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
```

On prépare le controlleur à recevoir les informations de tags et catégorie
```php
public function store(FormPostRequest $request)
{
    $post = Post::create($request->validated());
    $post->tags()->sync($request->validated('tags'));
    return redirect()
        ->route('blog.show', ['slug' => $post->slug, 'post' => $post->id])
        ->with('success', "L'article a bien été sauvegardé");
}

public function update(Post $post, FormPostRequest $request)
{
    // On update les champs validés en BDD
    $post->update($request->validated());
    // On synchronise les tags (car relation ManyToMany)
    $post->tags()->sync($request->validated('tags'));
    return redirect()
        ->route('blog.show', ['slug' => $post->slug, 'post' => $post->id])
        ->with('success', "L'article a bien été modifié");
}
```


## Authentification
On crée un AuthController, on assigne des routes de connexion / déconnexion et on crée une page de login
```php
// web.php
Route::get('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/login', [AuthController::class, 'doLogin']);
Route::delete('/logout', [AuthController::class, 'logout'])->name('auth.logout');

// AuthController
public function login()
{
    return view('auth.login');
}

public function logout()
{
    Auth::logout();
    return to_route('auth.login');
}


public function doLogin(LoginRequest $request)
{
    $credentials = $request->validated();

    if(Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended(route('blog.index'));
    }

    return to_route('auth.login')->withErrors([
        'email' => "Email invalide"
    ])->onlyInput('email');
}

// login.blade.php
@extends('base')


@section("content")

    <h1>Se connecter</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('auth.login') }}" method="post" class="vstack gap-3">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" name="password" id="password" class="form-control" >
                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary">
                    Se Connecter
                </button>

            </form>
        </div>
    </div>
@endsection
```
Dans app\Http\Middleware\Authenticate.php on modifie la route de redirection par défaut en cas d'accès à une page où il faut autre authentifié
```php
protected function redirectTo(Request $request): ?string
{
    return $request->expectsJson() ? null : route('auth.login');
}
```

## Système de fichiers
On crée une migration pour créer un champ image en BDD
```php
public function up(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->string('image')->nullable();
    });
}

/**
 * Reverse the migrations.
 */
public function down(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->dropColumn('image');
    });
}
```
On modifie les fonctions pour créer et update des articles pour pouvoir télécharger des images. On ajoute une fonction privée pour gérer les images (ajout / suppression)
```php
public function store(FormPostRequest $request)
{
    $post = Post::create($this->extractData(new Post(), $request));
    $post->tags()->sync($request->validated('tags'));
    return redirect()
        ->route('blog.show', ['slug' => $post->slug, 'post' => $post->id])
        ->with('success', "L'article a bien été sauvegardé");
}


public function update(Post $post, FormPostRequest $request)
{
    
    $post->update($this->extractData($post, $request));
    // On synchronise les tags (car relation ManyToMany)
    $post->tags()->sync($request->validated('tags'));
    return redirect()
        ->route('blog.show', ['slug' => $post->slug, 'post' => $post->id])
        ->with('success', "L'article a bien été modifié");
}

private function extractData(Post $post, FormPostRequest $request)
{
    $data = $request->validated();
    /**
     * @var UploadedFile|null $image
     */
    $image = $request->validated('image');
    if($image === null || $image->getError())
        return $data;
    if($post->image)
        Storage::disk('public')->delete($post->image);
    $data['image'] = $image->store('blog', 'public');
    return $data;
}

// Dans form.blade.php on ajoute un champ et on ajoute enctype="multipart/form-data" dans la balise form
<div class="mb-3">
    <label for="image" class="form-label">Titre</label>
    <input type="file" name="image" id="image" class="form-control">
    @error('image')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
```

On modifie le .env pour qu'il corresponde à l'URL du site
```.env
APP_URL=http://localhost:8000
```

On crée un lien vers le dossir de storage qui n'est pas accessible normalement
```
php artisan storage:link
```
