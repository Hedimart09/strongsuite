<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img
        src="/images/logo/logomark.png"
        alt="StrongSuite"
        :class="className"
        v-bind="$attrs"
    />
</template>
