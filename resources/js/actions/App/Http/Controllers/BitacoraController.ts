import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\BitacoraController::index
* @see app/Http/Controllers/BitacoraController.php:26
* @route '/bitacoras'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/bitacoras',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BitacoraController::index
* @see app/Http/Controllers/BitacoraController.php:26
* @route '/bitacoras'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BitacoraController::index
* @see app/Http/Controllers/BitacoraController.php:26
* @route '/bitacoras'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::index
* @see app/Http/Controllers/BitacoraController.php:26
* @route '/bitacoras'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BitacoraController::index
* @see app/Http/Controllers/BitacoraController.php:26
* @route '/bitacoras'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::index
* @see app/Http/Controllers/BitacoraController.php:26
* @route '/bitacoras'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::index
* @see app/Http/Controllers/BitacoraController.php:26
* @route '/bitacoras'
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
* @see \App\Http\Controllers\BitacoraController::create
* @see app/Http/Controllers/BitacoraController.php:86
* @route '/bitacoras/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/bitacoras/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BitacoraController::create
* @see app/Http/Controllers/BitacoraController.php:86
* @route '/bitacoras/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BitacoraController::create
* @see app/Http/Controllers/BitacoraController.php:86
* @route '/bitacoras/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::create
* @see app/Http/Controllers/BitacoraController.php:86
* @route '/bitacoras/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BitacoraController::create
* @see app/Http/Controllers/BitacoraController.php:86
* @route '/bitacoras/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::create
* @see app/Http/Controllers/BitacoraController.php:86
* @route '/bitacoras/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::create
* @see app/Http/Controllers/BitacoraController.php:86
* @route '/bitacoras/create'
*/
createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create.form = createForm

/**
* @see \App\Http\Controllers\BitacoraController::store
* @see app/Http/Controllers/BitacoraController.php:114
* @route '/bitacoras'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/bitacoras',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BitacoraController::store
* @see app/Http/Controllers/BitacoraController.php:114
* @route '/bitacoras'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BitacoraController::store
* @see app/Http/Controllers/BitacoraController.php:114
* @route '/bitacoras'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BitacoraController::store
* @see app/Http/Controllers/BitacoraController.php:114
* @route '/bitacoras'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BitacoraController::store
* @see app/Http/Controllers/BitacoraController.php:114
* @route '/bitacoras'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\BitacoraController::show
* @see app/Http/Controllers/BitacoraController.php:158
* @route '/bitacoras/{bitacora}'
*/
export const show = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/bitacoras/{bitacora}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BitacoraController::show
* @see app/Http/Controllers/BitacoraController.php:158
* @route '/bitacoras/{bitacora}'
*/
show.url = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { bitacora: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { bitacora: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            bitacora: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        bitacora: typeof args.bitacora === 'object'
        ? args.bitacora.id
        : args.bitacora,
    }

    return show.definition.url
            .replace('{bitacora}', parsedArgs.bitacora.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BitacoraController::show
* @see app/Http/Controllers/BitacoraController.php:158
* @route '/bitacoras/{bitacora}'
*/
show.get = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::show
* @see app/Http/Controllers/BitacoraController.php:158
* @route '/bitacoras/{bitacora}'
*/
show.head = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BitacoraController::show
* @see app/Http/Controllers/BitacoraController.php:158
* @route '/bitacoras/{bitacora}'
*/
const showForm = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::show
* @see app/Http/Controllers/BitacoraController.php:158
* @route '/bitacoras/{bitacora}'
*/
showForm.get = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::show
* @see app/Http/Controllers/BitacoraController.php:158
* @route '/bitacoras/{bitacora}'
*/
showForm.head = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\BitacoraController::edit
* @see app/Http/Controllers/BitacoraController.php:179
* @route '/bitacoras/{bitacora}/edit'
*/
export const edit = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/bitacoras/{bitacora}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BitacoraController::edit
* @see app/Http/Controllers/BitacoraController.php:179
* @route '/bitacoras/{bitacora}/edit'
*/
edit.url = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { bitacora: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { bitacora: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            bitacora: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        bitacora: typeof args.bitacora === 'object'
        ? args.bitacora.id
        : args.bitacora,
    }

    return edit.definition.url
            .replace('{bitacora}', parsedArgs.bitacora.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BitacoraController::edit
* @see app/Http/Controllers/BitacoraController.php:179
* @route '/bitacoras/{bitacora}/edit'
*/
edit.get = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::edit
* @see app/Http/Controllers/BitacoraController.php:179
* @route '/bitacoras/{bitacora}/edit'
*/
edit.head = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BitacoraController::edit
* @see app/Http/Controllers/BitacoraController.php:179
* @route '/bitacoras/{bitacora}/edit'
*/
const editForm = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::edit
* @see app/Http/Controllers/BitacoraController.php:179
* @route '/bitacoras/{bitacora}/edit'
*/
editForm.get = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::edit
* @see app/Http/Controllers/BitacoraController.php:179
* @route '/bitacoras/{bitacora}/edit'
*/
editForm.head = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\BitacoraController::update
* @see app/Http/Controllers/BitacoraController.php:228
* @route '/bitacoras/{bitacora}'
*/
export const update = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/bitacoras/{bitacora}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\BitacoraController::update
* @see app/Http/Controllers/BitacoraController.php:228
* @route '/bitacoras/{bitacora}'
*/
update.url = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { bitacora: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { bitacora: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            bitacora: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        bitacora: typeof args.bitacora === 'object'
        ? args.bitacora.id
        : args.bitacora,
    }

    return update.definition.url
            .replace('{bitacora}', parsedArgs.bitacora.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BitacoraController::update
* @see app/Http/Controllers/BitacoraController.php:228
* @route '/bitacoras/{bitacora}'
*/
update.put = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\BitacoraController::update
* @see app/Http/Controllers/BitacoraController.php:228
* @route '/bitacoras/{bitacora}'
*/
update.patch = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\BitacoraController::update
* @see app/Http/Controllers/BitacoraController.php:228
* @route '/bitacoras/{bitacora}'
*/
const updateForm = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BitacoraController::update
* @see app/Http/Controllers/BitacoraController.php:228
* @route '/bitacoras/{bitacora}'
*/
updateForm.put = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BitacoraController::update
* @see app/Http/Controllers/BitacoraController.php:228
* @route '/bitacoras/{bitacora}'
*/
updateForm.patch = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\BitacoraController::destroy
* @see app/Http/Controllers/BitacoraController.php:411
* @route '/bitacoras/{bitacora}'
*/
export const destroy = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/bitacoras/{bitacora}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\BitacoraController::destroy
* @see app/Http/Controllers/BitacoraController.php:411
* @route '/bitacoras/{bitacora}'
*/
destroy.url = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { bitacora: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { bitacora: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            bitacora: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        bitacora: typeof args.bitacora === 'object'
        ? args.bitacora.id
        : args.bitacora,
    }

    return destroy.definition.url
            .replace('{bitacora}', parsedArgs.bitacora.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BitacoraController::destroy
* @see app/Http/Controllers/BitacoraController.php:411
* @route '/bitacoras/{bitacora}'
*/
destroy.delete = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\BitacoraController::destroy
* @see app/Http/Controllers/BitacoraController.php:411
* @route '/bitacoras/{bitacora}'
*/
const destroyForm = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BitacoraController::destroy
* @see app/Http/Controllers/BitacoraController.php:411
* @route '/bitacoras/{bitacora}'
*/
destroyForm.delete = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const BitacoraController = { index, create, store, show, edit, update, destroy }

export default BitacoraController