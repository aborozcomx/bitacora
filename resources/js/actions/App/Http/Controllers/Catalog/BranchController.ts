import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Catalog\BranchController::index
* @see app/Http/Controllers/Catalog/BranchController.php:14
* @route '/catalogs/branches'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/catalogs/branches',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Catalog\BranchController::index
* @see app/Http/Controllers/Catalog/BranchController.php:14
* @route '/catalogs/branches'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\BranchController::index
* @see app/Http/Controllers/Catalog/BranchController.php:14
* @route '/catalogs/branches'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Catalog\BranchController::index
* @see app/Http/Controllers/Catalog/BranchController.php:14
* @route '/catalogs/branches'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Catalog\BranchController::store
* @see app/Http/Controllers/Catalog/BranchController.php:39
* @route '/catalogs/branches'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/catalogs/branches',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Catalog\BranchController::store
* @see app/Http/Controllers/Catalog/BranchController.php:39
* @route '/catalogs/branches'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\BranchController::store
* @see app/Http/Controllers/Catalog/BranchController.php:39
* @route '/catalogs/branches'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Catalog\BranchController::update
* @see app/Http/Controllers/Catalog/BranchController.php:54
* @route '/catalogs/branches/{branch}'
*/
export const update = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/catalogs/branches/{branch}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Catalog\BranchController::update
* @see app/Http/Controllers/Catalog/BranchController.php:54
* @route '/catalogs/branches/{branch}'
*/
update.url = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{branch}', parsedArgs.branch.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\BranchController::update
* @see app/Http/Controllers/Catalog/BranchController.php:54
* @route '/catalogs/branches/{branch}'
*/
update.put = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Catalog\BranchController::update
* @see app/Http/Controllers/Catalog/BranchController.php:54
* @route '/catalogs/branches/{branch}'
*/
update.patch = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Catalog\BranchController::destroy
* @see app/Http/Controllers/Catalog/BranchController.php:69
* @route '/catalogs/branches/{branch}'
*/
export const destroy = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/catalogs/branches/{branch}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Catalog\BranchController::destroy
* @see app/Http/Controllers/Catalog/BranchController.php:69
* @route '/catalogs/branches/{branch}'
*/
destroy.url = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{branch}', parsedArgs.branch.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Catalog\BranchController::destroy
* @see app/Http/Controllers/Catalog/BranchController.php:69
* @route '/catalogs/branches/{branch}'
*/
destroy.delete = (args: { branch: number | { id: number } } | [branch: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

const BranchController = { index, store, update, destroy }

export default BranchController