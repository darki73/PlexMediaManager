<template>
    <v-text-field
        v-model="content"
        :label="label"
        :placeholder="placeholder"
        :hint="required ? $t('common.field_required') : hint"
        :persistent-hint="persistentHint || required"
        :outlined="outlined"
        :append-icon="sensitive ? (show ? 'visibility' : 'visibility_off') : ''"
        :type="sensitive ? (show ? 'text' : 'password') : 'text'"
        @click:append="sensitive ? (show = !show) : ''"
        @input="handleInput"

    />
</template>
<script>
    export default {
        name: 'v-sensitive-input',
        props: {
            value: {
                required: false
            },
            label: {
                type: String,
                required: false,
                default: () => ''
            },
            placeholder: {
                type: String,
                required: false,
                default: () => ''
            },
            hint: {
                type: String,
                required: false,
                default: () => ''
            },
            persistentHint: {
                type: Boolean,
                required: false,
                default: () => false
            },
            outlined: {
                type: Boolean,
                required: false,
                default: () => false
            },
            sensitive: {
                type: Boolean,
                required: false,
                default: () => false
            },
            required: {
                type: Boolean,
                required: false,
                default: () => false
            }
        },
        data: () => ({
            show: false,
            content: ''
        }),
        watch: {
            value (current, previous) {
                this.content = current;
            }
        },
        mounted() {
            this.content = this.value;
        },
        methods: {
            handleInput(event) {
                this.$emit('input', this.content);
            }
        }
    };
</script>
