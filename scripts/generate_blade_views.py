#!/usr/bin/env python3
"""Generate remaining AutoSpa Blade view files."""
import os

BASE = os.path.join(os.path.dirname(__file__), "..", "resources", "views")
IC = "mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"

created = []


def write(path, content):
    full = os.path.normpath(os.path.join(BASE, path))
    os.makedirs(os.path.dirname(full), exist_ok=True)
    with open(full, "w", encoding="utf-8", newline="\n") as f:
        f.write(content.strip() + "\n")
    created.append(path)


def layout(title, body):
    return f"""<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{title}</h1></x-slot>
{body}
</x-layouts.app>"""


def index_page(title, create_route, create_label, paginator, empty_title, empty_desc, table_head, table_body):
    return layout(
        title,
        f"""    {index_header(title, create_route, create_label)}

    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
{table_head}
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
{table_body}
                </tbody>
            </table>
        </div>
        @if (${paginator}->isEmpty())
            <x-ui.empty-state title="{empty_title}" description="{empty_desc}" />
        @endif
        @include('partials.crud.pagination', ['paginator' => ${paginator}])
    </x-ui.card>""",
    )


def index_header(title, create_route, create_label):
    return f"""@include('partials.crud.index-header', ['createRoute' => route('{create_route}'), 'createLabel' => '{create_label}'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">{title}</h1>
    @endinclude"""


def th(cols):
    return "\n".join(
        f'                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">{c}</th>'
        for c in cols[:-1]
    ) + (
        f'\n                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">{cols[-1]}</th>'
        if cols
        else ""
    )


def actions(route_prefix, var):
    return f"""                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{{{ route('{route_prefix}.show', ${var}) }}}}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{{{ route('{route_prefix}.edit', ${var}) }}}}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>"""


def actions_view_only(route_prefix, var):
    return f"""                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{{{ route('{route_prefix}.show', ${var}) }}}}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                            </td>"""


def create_page(title, store_route, index_route, form_view, submit):
    return layout(
        title,
        f"""    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{{{ route('{store_route}') }}}}" class="space-y-6">
            @csrf
            @include('{form_view}')
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>{submit}</x-primary-button>
                <a href="{{{{ route('{index_route}') }}}}" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Cancel</a>
            </div>
        </form>
    </x-ui.card>""",
    )


def edit_page(title, update_route, show_route, form_view, model_var, submit):
    return layout(
        title,
        f"""    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{{{ route('{update_route}', ${model_var}) }}}}" class="space-y-6">
            @csrf @method('PUT')
            @include('{form_view}', ['{model_var}' => ${model_var}])
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>{submit}</x-primary-button>
                <a href="{{{{ route('{show_route}', ${model_var}) }}}}" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Cancel</a>
            </div>
        </form>
    </x-ui.card>""",
    )


def show_page(title_expr, back_route, edit_route, delete_route, delete_confirm, cards_html):
    return f"""<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{title_expr}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('{back_route}'),
            'editRoute' => route('{edit_route}'),
            'deleteRoute' => route('{delete_route}'),
            'deleteConfirm' => '{delete_confirm}',
        ])
    </div>

{cards_html}
</x-layouts.app>"""


def show_page_no_delete(title_expr, back_route, edit_route, cards_html):
    return f"""<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{title_expr}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('{back_route}'),
            'editRoute' => route('{edit_route}'),
        ])
    </div>

{cards_html}
</x-layouts.app>"""


def show_page_view_only(title_expr, back_route, cards_html):
    return f"""<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{title_expr}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('{back_route}'),
        ])
    </div>

{cards_html}
</x-layouts.app>"""


def dl_card(title, rows):
    rows_html = "\n".join(
        f'            <div class="flex justify-between gap-4"><dt class="text-slate-500">{k}</dt><dd{class_attr}>{v}</dd></div>'
        for k, v, *rest in rows
        for class_attr in [f' class="font-medium"' if rest and rest[0] == "bold" else ""]
    )
    return f"""    <x-ui.card>
        <h2 class="mb-4 text-lg font-semibold">{title}</h2>
        <dl class="space-y-3 text-sm">
{rows_html}
        </dl>
    </x-ui.card>"""


def active_badge(field="$item"):
    return f"@if({field}->is_active)<x-ui.badge color=\"green\">Active</x-ui.badge>@else<x-ui.badge color=\"slate\">Inactive</x-ui.badge>@endif"


def text_field(name, label, model_var, type_="text", required=True, span=1):
    req = " required" if required else ""
    t = f' type="{type_}"' if type_ != "text" else ""
    col = " sm:col-span-2" if span == 2 else ""
    return f"""    <div class="{col.strip() or ''}">
        <x-input-label for="{name}" value="{label}" />
        <x-text-input id="{name}" name="{name}"{t} class="{IC}" :value="old('{name}', ${model_var}->{name} ?? '')"{req} />
        <x-input-error :messages="$errors->get('{name}')" />
    </div>"""


def textarea_field(name, label, model_var, span=2):
    return f"""    <div class="sm:col-span-{span}">
        <x-input-label for="{name}" value="{label}" />
        <textarea id="{name}" name="{name}" rows="3" class="{IC}">{{ old('{name}', ${model_var}->{name} ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('{name}')" />
    </div>"""


def checkbox_active(model_var):
    return f"""    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', ${model_var}->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>"""


def select_field(name, label, options_var, model_var, display="name", required=True):
    req = " required" if required else ""
    return f"""    <div>
        <x-input-label for="{name}" value="{label}" />
        <select id="{name}" name="{name}" class="{IC}"{req}>
            <option value="">Select…</option>
            @foreach (${options_var} as $opt)
                <option value="{{{{ $opt->id }}}}" @selected(old('{name}', ${model_var}->{name} ?? '') == $opt->id)>{{{{ $opt->{display} }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('{name}')" />
    </div>"""


# ============ SETTINGS: ROLES ============
write(
    "settings/roles/edit.blade.php",
    layout(
        "{{ $role->name }}",
        """    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('settings.roles.update', $role) }}" class="space-y-6">
            @csrf @method('PUT')
            <div>
                <x-input-label value="Permissions" />
                <div class="mt-2 flex flex-wrap gap-3">
                    @foreach ($permissions as $permission)
                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-700">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())))>
                            <span class="text-sm">{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('permissions')" />
            </div>
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>Save Permissions</x-primary-button>
                <a href="{{ route('settings.roles.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Cancel</a>
            </div>
        </form>
    </x-ui.card>""",
    ),
)

write(
    "settings/roles/index.blade.php",
    """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Roles</h1></x-slot>

    @include('partials.crud.index-header', ['createRoute' => null, 'createLabel' => 'Add Role'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Roles</h1>
    @endinclude

    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Permissions</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($roles as $role)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $role->name }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $role->permissions->count() }} permissions</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('settings.roles.edit', $role) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($roles->isEmpty())
            <x-ui.empty-state title="No roles yet" description="Roles define what team members can access." />
        @endif
    </x-ui.card>
</x-layouts.app>""",
)

# ============ SETTINGS: TAXES ============
write(
    "settings/taxes/_form.blade.php",
    f"""@php $tax = $tax ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Tax Name" />
        <x-text-input id="name" name="name" class="{IC}" :value="old('name', $tax->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="code" value="Code" />
        <x-text-input id="code" name="code" class="{IC}" :value="old('code', $tax->code ?? '')" required />
        <x-input-error :messages="$errors->get('code')" />
    </div>
    <div>
        <x-input-label for="rate" value="Rate (%)" />
        <x-text-input id="rate" name="rate" type="number" step="0.01" class="{IC}" :value="old('rate', $tax->rate ?? '')" required />
        <x-input-error :messages="$errors->get('rate')" />
    </div>
    <div class="sm:col-span-2 flex flex-wrap gap-6">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $tax->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_default" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_default', $tax->is_default ?? false))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Default tax</span>
        </label>
    </div>
</div>""",
)

write("settings/taxes/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Taxes</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('settings.taxes.create'), 'createLabel' => 'Add Tax'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Taxes</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Rate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($taxes as $tax)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $tax->name }} @if($tax->is_default)<x-ui.badge color="indigo">Default</x-ui.badge>@endif</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $tax->code }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $tax->rate }}%</td>
                            <td class="whitespace-nowrap px-6 py-4">@if($tax->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('settings.taxes.show', $tax) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('settings.taxes.edit', $tax) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($taxes->isEmpty())<x-ui.empty-state title="No taxes configured" description="Add tax rates for invoicing and POS." />@endif
        @include('partials.crud.pagination', ['paginator' => $taxes])
    </x-ui.card>
</x-layouts.app>""")

write("settings/taxes/create.blade.php", create_page("Add Tax", "settings.taxes.store", "settings.taxes.index", "settings.taxes._form", "Create Tax"))
write("settings/taxes/edit.blade.php", edit_page("Edit Tax", "settings.taxes.update", "settings.taxes.show", "settings.taxes._form", "tax", "Save Changes"))
write(
    "settings/taxes/show.blade.php",
    show_page(
        "{{ $tax->name }}",
        "settings.taxes.index",
        "settings.taxes.edit",
        "settings.taxes.destroy",
        "Delete this tax?",
        """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Code</dt><dd class="font-medium">{{ $tax->code }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Rate</dt><dd>{{ $tax->rate }}%</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($tax->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Default</dt><dd>@if($tax->is_default)<x-ui.badge color="indigo">Yes</x-ui.badge>@else No @endif</dd></div>
        </dl>
    </x-ui.card>""",
    ),
)

# Part 1 complete (roles + taxes)
