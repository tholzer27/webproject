<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import { Moon, Sun } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const { updateAppearance } = useAppearance();
const isDark = ref(false);

onMounted(() => {
    isDark.value = document.documentElement.classList.contains('dark');
});

const toggleTheme = () => {
    const nextTheme = isDark.value ? 'light' : 'dark';

    updateAppearance(nextTheme);
    isDark.value = nextTheme === 'dark';
};
</script>

<template>
    <Button
        type="button"
        variant="ghost"
        size="icon"
        class="h-9 w-9"
        :aria-label="isDark ? 'Light mode aktivieren' : 'Dark mode aktivieren'"
        @click="toggleTheme"
    >
        <Sun v-if="isDark" class="size-4" />
        <Moon v-else class="size-4" />
    </Button>
</template>
