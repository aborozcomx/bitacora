import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Catalog\FolioController::index
* @see app/Http/Controllers/Catalog/FolioController.php:14
* @route '/catalogs/folios'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/catalogs/folios',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Catalog\FolioController::index
* @see app/Http/Controllers/Catalog/FolioController.php:14
* @route '/catalogs/folios'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\FolioController::index
* @see app/Http/Controllers/Catalog/FolioController.php:14
* @route '/catalogs/folios'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Catalog\FolioController::index
* @see app/Http/Controllers/Catalog/FolioController.php:14
* @route '/catalogs/folios'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Catalog\FolioController::index
* @see app/Http/Controllers/Catalog/FolioController.php:14
* @route '/catalogs/folios'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Catalog\FolioController::index
* @see app/Http/Controllers/Catalog/FolioController.php:14
* @route '/catalogs/folios'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Catalog\FolioController::index
* @see app/Http/Controllers/Catalog/FolioController.php:14
* @route '/catalogs/folios'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\Catalog\FolioController::store
* @see app/Http/Controllers/Catalog/FolioController.php:39
* @route '/catalogs/folios'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/catalogs/folios',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Catalog\FolioController::store
* @see app/Http/Controllers/Catalog/FolioController.php:39
* @route '/catalogs/folios'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\FolioController::store
* @see app/Http/Controllers/Catalog/FolioController.php:39
* @route '/catalogs/folios'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\FolioController::store
* @see app/Http/Controllers/Catalog/FolioController.php:39
* @route '/catalogs/folios'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\FolioController::store
* @see app/Http/Controllers/Catalog/FolioController.php:39
* @route '/catalogs/folios'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Catalog\FolioController::update
* @see app/Http/Controllers/Catalog/FolioController.php:57
* @route '/catalogs/folios/{folio}'
*/
export const update = (args: { folio: number | { id: number } } | [folio: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/catalogs/folios/{folio}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Catalog\FolioController::update
* @see app/Http/Controllers/Catalog/FolioController.php:57
* @route '/catalogs/folios/{folio}'
*/
update.url = (args: { folio: number | { id: number } } | [folio: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { folio: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { folio: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            folio: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        folio: typeof args.folio === 'object'
        ? args.folio.id
        : args.folio,
    }

    return update.definition.url
            .replace('{folio}', parsedArgs.folio.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\FolioController::update
* @see app/Http/Controllers/Catalog/FolioController.php:57
* @route '/catalogs/folios/{folio}'
*/
update.put = (args: { folio: number | { id: number } } | [folio: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Catalog\FolioController::update
* @see app/Http/Controllers/Catalog/FolioController.php:57
* @route '/catalogs/folios/{folio}'
*/
update.patch = (args: { folio: number | { id: number } } | [folio: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Catalog\FolioController::update
* @see app/Http/Controllers/Catalog/FolioController.php:57
* @route '/catalogs/folios/{folio}'
*/
const updateForm = (args: { folio: number | { id: number } } | [folio: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\FolioController::update
* @see app/Http/Controllers/Catalog/FolioController.php:57
* @route '/catalogs/folios/{folio}'
*/
updateForm.put = (args: { folio: number | { id: number } } | [folio: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\FolioController::update
* @see app/Http/Controllers/Catalog/FolioController.php:57
* @route '/catalogs/folios/{folio}'
*/
updateForm.patch = (args: { folio: number | { id: number } } | [folio: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\Catalog\FolioController::destroy
* @see app/Http/Controllers/Catalog/FolioController.php:74
* @route '/catalogs/folios/{folio}'
*/
export const destroy = (args: { folio: number | { id: number } } | [folio: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/catalogs/folios/{folio}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Catalog\FolioController::destroy
* @see app/Http/Controllers/Catalog/FolioController.php:74
* @route '/catalogs/folios/{folio}'
*/
destroy.url = (args: { folio: number | { id: number } } | [folio: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { folio: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { folio: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            folio: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        folio: typeof args.folio === 'object'
        ? args.folio.id
        : args.folio,
    }

    return destroy.definition.url
            .replace('{folio}', parsedArgs.folio.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\FolioController::destroy
* @see app/Http/Controllers/Catalog/FolioController.php:74
* @route '/catalogs/folios/{folio}'
*/
destroy.delete = (args: { folio: number | { id: number } } | [folio: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Catalog\FolioController::destroy
* @see app/Http/Controllers/Catalog/FolioController.php:74
* @route '/catalogs/folios/{folio}'
*/
const destroyForm = (args: { folio: number | { id: number } } | [folio: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\FolioController::destroy
* @see app/Http/Controllers/Catalog/FolioController.php:74
* @route '/catalogs/folios/{folio}'
*/
destroyForm.delete = (args: { folio: number | { id: number } } | [folio: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const folios = {
    index: Object.assign(index, index),
    store: Object.assign(store, store),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
}

export default folios