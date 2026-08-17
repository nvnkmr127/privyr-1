<v-button {{ $attributes }}></v-button>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-button-template"
    >
        <button
            v-bind="$attrs"
            :class="[buttonClass, loading ? 'flex items-center justify-center gap-2' : '']"
            :disabled="loading || $attrs.disabled"
        >
            <x-admin::spinner v-if="loading" class="relative" />

            <span v-if="loading && loadingTitle">
                @{{ loadingTitle }}
            </span>
            
            <span v-else :class="loading ? 'relative h-full w-full opacity-0' : ''">
                @{{ title }}
            </span>
        </button>
    </script>

    <script type="module">
        app.component('v-button', {
            template: '#v-button-template',

            props: {
                loadingTitle: String,
                loading: Boolean,
                buttonType: String,
                title: String,
                buttonClass: String,
            },
        });
    </script>
@endPushOnce