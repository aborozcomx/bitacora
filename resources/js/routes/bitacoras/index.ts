import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\BitacoraController::finalized
* @see app/Http/Controllers/BitacoraController.php:175
* @route '/bitacoras-finalizadas'
*/
export const finalized = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: finalized.url(options),
    method: 'get',
})

finalized.definition = {
    methods: ["get","head"],
    url: '/bitacoras-finalizadas',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BitacoraController::finalized
* @see app/Http/Controllers/BitacoraController.php:175
* @route '/bitacoras-finalizadas'
*/
finalized.url = (options?: RouteQueryOptions) => {
    return finalized.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BitacoraController::finalized
* @see app/Http/Controllers/BitacoraController.php:175
* @route '/bitacoras-finalizadas'
*/
finalized.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: finalized.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::finalized
* @see app/Http/Controllers/BitacoraController.php:175
* @route '/bitacoras-finalizadas'
*/
finalized.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: finalized.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BitacoraController::close
* @see app/Http/Controllers/BitacoraController.php:324
* @route '/bitacoras/{bitacora}/close'
*/
export const close = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: close.url(args, options),
    method: 'post',
})

close.definition = {
    methods: ["post"],
    url: '/bitacoras/{bitacora}/close',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BitacoraController::close
* @see app/Http/Controllers/BitacoraController.php:324
* @route '/bitacoras/{bitacora}/close'
*/
close.url = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return close.definition.url
            .replace('{bitacora}', parsedArgs.bitacora.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BitacoraController::close
* @see app/Http/Controllers/BitacoraController.php:324
* @route '/bitacoras/{bitacora}/close'
*/
close.post = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: close.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BitacoraController::index
* @see app/Http/Controllers/BitacoraController.php:28
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
* @see app/Http/Controllers/BitacoraController.php:28
* @route '/bitacoras'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BitacoraController::index
* @see app/Http/Controllers/BitacoraController.php:28
* @route '/bitacoras'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::index
* @see app/Http/Controllers/BitacoraController.php:28
* @route '/bitacoras'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BitacoraController::create
* @see app/Http/Controllers/BitacoraController.php:339
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
* @see app/Http/Controllers/BitacoraController.php:339
* @route '/bitacoras/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BitacoraController::create
* @see app/Http/Controllers/BitacoraController.php:339
* @route '/bitacoras/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::create
* @see app/Http/Controllers/BitacoraController.php:339
* @route '/bitacoras/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BitacoraController::store
* @see app/Http/Controllers/BitacoraController.php:432
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
* @see app/Http/Controllers/BitacoraController.php:432
* @route '/bitacoras'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BitacoraController::store
* @see app/Http/Controllers/BitacoraController.php:432
* @route '/bitacoras'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BitacoraController::show
* @see app/Http/Controllers/BitacoraController.php:528
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
* @see app/Http/Controllers/BitacoraController.php:528
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
* @see app/Http/Controllers/BitacoraController.php:528
* @route '/bitacoras/{bitacora}'
*/
show.get = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::show
* @see app/Http/Controllers/BitacoraController.php:528
* @route '/bitacoras/{bitacora}'
*/
show.head = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BitacoraController::edit
* @see app/Http/Controllers/BitacoraController.php:549
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
* @see app/Http/Controllers/BitacoraController.php:549
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
* @see app/Http/Controllers/BitacoraController.php:549
* @route '/bitacoras/{bitacora}/edit'
*/
edit.get = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BitacoraController::edit
* @see app/Http/Controllers/BitacoraController.php:549
* @route '/bitacoras/{bitacora}/edit'
*/
edit.head = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BitacoraController::update
* @see app/Http/Controllers/BitacoraController.php:626
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
* @see app/Http/Controllers/BitacoraController.php:626
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
* @see app/Http/Controllers/BitacoraController.php:626
* @route '/bitacoras/{bitacora}'
*/
update.put = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\BitacoraController::update
* @see app/Http/Controllers/BitacoraController.php:626
* @route '/bitacoras/{bitacora}'
*/
update.patch = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\BitacoraController::destroy
* @see app/Http/Controllers/BitacoraController.php:938
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
* @see app/Http/Controllers/BitacoraController.php:938
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
* @see app/Http/Controllers/BitacoraController.php:938
* @route '/bitacoras/{bitacora}'
*/
destroy.delete = (args: { bitacora: number | { id: number } } | [bitacora: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

const bitacoras = {
    finalized: Object.assign(finalized, finalized),
    close: Object.assign(close, close),
    index: Object.assign(index, index),
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    show: Object.assign(show, show),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
}

export default bitacoras