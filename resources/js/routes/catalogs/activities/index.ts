import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::index
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:14
* @route '/catalogs/activities'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/catalogs/activities',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::index
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:14
* @route '/catalogs/activities'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::index
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:14
* @route '/catalogs/activities'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::index
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:14
* @route '/catalogs/activities'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::index
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:14
* @route '/catalogs/activities'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::index
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:14
* @route '/catalogs/activities'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::index
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:14
* @route '/catalogs/activities'
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
* @see \App\Http\Controllers\Catalog\ActivityTypeController::store
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:31
* @route '/catalogs/activities'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/catalogs/activities',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::store
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:31
* @route '/catalogs/activities'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::store
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:31
* @route '/catalogs/activities'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::store
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:31
* @route '/catalogs/activities'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::store
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:31
* @route '/catalogs/activities'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::update
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:44
* @route '/catalogs/activities/{activity}'
*/
export const update = (args: { activity: string | number } | [activity: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/catalogs/activities/{activity}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::update
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:44
* @route '/catalogs/activities/{activity}'
*/
update.url = (args: { activity: string | number } | [activity: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { activity: args }
    }

    if (Array.isArray(args)) {
        args = {
            activity: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        activity: args.activity,
    }

    return update.definition.url
            .replace('{activity}', parsedArgs.activity.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::update
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:44
* @route '/catalogs/activities/{activity}'
*/
update.put = (args: { activity: string | number } | [activity: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::update
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:44
* @route '/catalogs/activities/{activity}'
*/
update.patch = (args: { activity: string | number } | [activity: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::update
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:44
* @route '/catalogs/activities/{activity}'
*/
const updateForm = (args: { activity: string | number } | [activity: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::update
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:44
* @route '/catalogs/activities/{activity}'
*/
updateForm.put = (args: { activity: string | number } | [activity: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::update
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:44
* @route '/catalogs/activities/{activity}'
*/
updateForm.patch = (args: { activity: string | number } | [activity: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Catalog\ActivityTypeController::destroy
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:57
* @route '/catalogs/activities/{activity}'
*/
export const destroy = (args: { activity: string | number } | [activity: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/catalogs/activities/{activity}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::destroy
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:57
* @route '/catalogs/activities/{activity}'
*/
destroy.url = (args: { activity: string | number } | [activity: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { activity: args }
    }

    if (Array.isArray(args)) {
        args = {
            activity: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        activity: args.activity,
    }

    return destroy.definition.url
            .replace('{activity}', parsedArgs.activity.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::destroy
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:57
* @route '/catalogs/activities/{activity}'
*/
destroy.delete = (args: { activity: string | number } | [activity: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::destroy
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:57
* @route '/catalogs/activities/{activity}'
*/
const destroyForm = (args: { activity: string | number } | [activity: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\ActivityTypeController::destroy
* @see app/Http/Controllers/Catalog/ActivityTypeController.php:57
* @route '/catalogs/activities/{activity}'
*/
destroyForm.delete = (args: { activity: string | number } | [activity: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const activities = {
    index: Object.assign(index, index),
    store: Object.assign(store, store),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
}

export default activities