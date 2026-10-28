<script setup lang="ts">
import type { Component } from 'vue';
import { PackageSearch } from 'lucide-vue-next';

withDefaults(defineProps<{ title: string; text?: string; icon?: Component }>(), { icon: () => PackageSearch });
</script>

<template>
    <div class="card flex flex-col items-center px-6 py-14 text-center">
        <span class="grid size-14 place-items-center rounded-2xl bg-primary-50 text-primary">
            <component :is="icon" class="size-7" />
        </span>
        <h3 class="mt-4 text-lg font-bold text-ink">{{ title }}</h3>
        <p v-if="text" class="mt-1 max-w-md text-sm text-muted">{{ text }}</p>
        <div class="mt-5 empty:hidden"><slot /></div>
    </div>
</template>
