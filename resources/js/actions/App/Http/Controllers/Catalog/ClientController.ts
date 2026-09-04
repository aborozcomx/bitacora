import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Catalog\ClientController::index
* @see app/Http/Controllers/Catalog/ClientController.php:15
* @route '/catalogs/clients'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/catalogs/clients',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Catalog\ClientController::index
* @see app/Http/Controllers/Catalog/ClientController.php:15
* @route '/catalogs/clients'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\ClientController::index
* @see app/Http/Controllers/Catalog/ClientController.php:15
* @route '/catalogs/clients'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::index
* @see app/Http/Controllers/Catalog/ClientController.php:15
* @route '/catalogs/clients'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::index
* @see app/Http/Controllers/Catalog/ClientController.php:15
* @route '/catalogs/clients'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::index
* @see app/Http/Controllers/Catalog/ClientController.php:15
* @route '/catalogs/clients'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::index
* @see app/Http/Controllers/Catalog/ClientController.php:15
* @route '/catalogs/clients'
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
* @see \App\Http\Controllers\Catalog\ClientController::store
* @see app/Http/Controllers/Catalog/ClientController.php:34
* @route '/catalogs/clients'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/catalogs/clients',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Catalog\ClientController::store
* @see app/Http/Controllers/Catalog/ClientController.php:34
* @route '/catalogs/clients'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\ClientController::store
* @see app/Http/Controllers/Catalog/ClientController.php:34
* @route '/catalogs/clients'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::store
* @see app/Http/Controllers/Catalog/ClientController.php:34
* @route '/catalogs/clients'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::store
* @see app/Http/Controllers/Catalog/ClientController.php:34
* @route '/catalogs/clients'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Catalog\ClientController::update
* @see app/Http/Controllers/Catalog/ClientController.php:50
* @route '/catalogs/clients/{client}'
*/
export const update = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/catalogs/clients/{client}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Catalog\ClientController::update
* @see app/Http/Controllers/Catalog/ClientController.php:50
* @route '/catalogs/clients/{client}'
*/
update.url = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { client: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { client: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            client: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        client: typeof args.client === 'object'
        ? args.client.id
        : args.client,
    }

    return update.definition.url
            .replace('{client}', parsedArgs.client.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\ClientController::update
* @see app/Http/Controllers/Catalog/ClientController.php:50
* @route '/catalogs/clients/{client}'
*/
update.put = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::update
* @see app/Http/Controllers/Catalog/ClientController.php:50
* @route '/catalogs/clients/{client}'
*/
update.patch = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::update
* @see app/Http/Controllers/Catalog/ClientController.php:50
* @route '/catalogs/clients/{client}'
*/
const updateForm = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::update
* @see app/Http/Controllers/Catalog/ClientController.php:50
* @route '/catalogs/clients/{client}'
*/
updateForm.put = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::update
* @see app/Http/Controllers/Catalog/ClientController.php:50
* @route '/catalogs/clients/{client}'
*/
updateForm.patch = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Catalog\ClientController::destroy
* @see app/Http/Controllers/Catalog/ClientController.php:66
* @route '/catalogs/clients/{client}'
*/
export const destroy = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/catalogs/clients/{client}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Catalog\ClientController::destroy
* @see app/Http/Controllers/Catalog/ClientController.php:66
* @route '/catalogs/clients/{client}'
*/
destroy.url = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { client: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { client: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            client: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        client: typeof args.client === 'object'
        ? args.client.id
        : args.client,
    }

    return destroy.definition.url
            .replace('{client}', parsedArgs.client.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\ClientController::destroy
* @see app/Http/Controllers/Catalog/ClientController.php:66
* @route '/catalogs/clients/{client}'
*/
destroy.delete = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::destroy
* @see app/Http/Controllers/Catalog/ClientController.php:66
* @route '/catalogs/clients/{client}'
*/
const destroyForm = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::destroy
* @see app/Http/Controllers/Catalog/ClientController.php:66
* @route '/catalogs/clients/{client}'
*/
destroyForm.delete = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

/**
* @see \App\Http\Controllers\Catalog\ClientController::storeBranch
* @see app/Http/Controllers/Catalog/ClientController.php:73
* @route '/catalogs/clients/{client}/branches'
*/
export const storeBranch = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeBranch.url(args, options),
    method: 'post',
})

storeBranch.definition = {
    methods: ["post"],
    url: '/catalogs/clients/{client}/branches',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Catalog\ClientController::storeBranch
* @see app/Http/Controllers/Catalog/ClientController.php:73
* @route '/catalogs/clients/{client}/branches'
*/
storeBranch.url = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { client: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { client: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            client: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        client: typeof args.client === 'object'
        ? args.client.id
        : args.client,
    }

    return storeBranch.definition.url
            .replace('{client}', parsedArgs.client.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\ClientController::storeBranch
* @see app/Http/Controllers/Catalog/ClientController.php:73
* @route '/catalogs/clients/{client}/branches'
*/
storeBranch.post = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeBranch.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::storeBranch
* @see app/Http/Controllers/Catalog/ClientController.php:73
* @route '/catalogs/clients/{client}/branches'
*/
const storeBranchForm = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeBranch.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::storeBranch
* @see app/Http/Controllers/Catalog/ClientController.php:73
* @route '/catalogs/clients/{client}/branches'
*/
storeBranchForm.post = (args: { client: number | { id: number } } | [client: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeBranch.url(args, options),
    method: 'post',
})

storeBranch.form = storeBranchForm

/**
* @see \App\Http\Controllers\Catalog\ClientController::updateBranch
* @see app/Http/Controllers/Catalog/ClientController.php:88
* @route '/catalogs/clients/branches/{branch}'
*/
export const updateBranch = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateBranch.url(args, options),
    method: 'put',
})

updateBranch.definition = {
    methods: ["put"],
    url: '/catalogs/clients/branches/{branch}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Catalog\ClientController::updateBranch
* @see app/Http/Controllers/Catalog/ClientController.php:88
* @route '/catalogs/clients/branches/{branch}'
*/
updateBranch.url = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { branch: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { branch: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            branch: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        branch: typeof args.branch === 'object'
        ? args.branch.id
        : args.branch,
    }

    return updateBranch.definition.url
            .replace('{branch}', parsedArgs.branch.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\ClientController::updateBranch
* @see app/Http/Controllers/Catalog/ClientController.php:88
* @route '/catalogs/clients/branches/{branch}'
*/
updateBranch.put = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateBranch.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::updateBranch
* @see app/Http/Controllers/Catalog/ClientController.php:88
* @route '/catalogs/clients/branches/{branch}'
*/
const updateBranchForm = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateBranch.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::updateBranch
* @see app/Http/Controllers/Catalog/ClientController.php:88
* @route '/catalogs/clients/branches/{branch}'
*/
updateBranchForm.put = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateBranch.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updateBranch.form = updateBranchForm

/**
* @see \App\Http\Controllers\Catalog\ClientController::destroyBranch
* @see app/Http/Controllers/Catalog/ClientController.php:103
* @route '/catalogs/clients/branches/{branch}'
*/
export const destroyBranch = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyBranch.url(args, options),
    method: 'delete',
})

destroyBranch.definition = {
    methods: ["delete"],
    url: '/catalogs/clients/branches/{branch}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Catalog\ClientController::destroyBranch
* @see app/Http/Controllers/Catalog/ClientController.php:103
* @route '/catalogs/clients/branches/{branch}'
*/
destroyBranch.url = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { branch: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { branch: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            branch: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        branch: typeof args.branch === 'object'
        ? args.branch.id
        : args.branch,
    }

    return destroyBranch.definition.url
            .replace('{branch}', parsedArgs.branch.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\ClientController::destroyBranch
* @see app/Http/Controllers/Catalog/ClientController.php:103
* @route '/catalogs/clients/branches/{branch}'
*/
destroyBranch.delete = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyBranch.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::destroyBranch
* @see app/Http/Controllers/Catalog/ClientController.php:103
* @route '/catalogs/clients/branches/{branch}'
*/
const destroyBranchForm = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyBranch.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\ClientController::destroyBranch
* @see app/Http/Controllers/Catalog/ClientController.php:103
* @route '/catalogs/clients/branches/{branch}'
*/
destroyBranchForm.delete = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyBranch.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroyBranch.form = destroyBranchForm

const ClientController = { index, store, update, destroy, storeBranch, updateBranch, destroyBranch }

export default ClientController