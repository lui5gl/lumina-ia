<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const message = ref('');
const processing = ref(false);
const error = ref('');
const messages = ref<string[]>([]);

const submit = async () => {
    if (!message.value.trim()) return;

    messages.value.push(`Tú: ${message.value}`);
    processing.value = true;
    error.value = '';

    try {
        const response = await fetch('/api/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: message.value }),
        });

        const data = await response.json();

        if (response.ok) {
            messages.value.push(`IA: ${data.reply}`);
        } else {
            error.value = 'Error del servidor';
            messages.value.push('IA: Error al procesar la respuesta.');
        }
    } catch (e) {
        error.value = 'Error de conexión';
        messages.value.push('IA: No se pudo conectar al servidor.');
    }

    message.value = '';
    processing.value = false;
};
</script>

<template>
    <Head title="Nuevo Chat" />

    <AppLayout>
        <div class="flex h-[calc(100vh-4rem)] flex-col">
            <!-- Área de mensajes -->
            <div class="flex-1 space-y-4 overflow-y-auto p-6">
                <div v-for="(msg, index) in messages" :key="index" class="text-sm whitespace-pre-line">
                    {{ msg }}
                </div>
                <div v-if="messages.length === 0" class="text-sm text-muted-foreground">Sin mensajes aún</div>
            </div>

            <!-- Área de entrada de mensaje -->
            <form @submit.prevent="submit" class="border-t border-border p-4">
                <div class="flex items-end gap-2">
                    <textarea
                        v-model="message"
                        placeholder="Pregunta lo que quieras"
                        rows="2"
                        class="w-full resize-none rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                    ></textarea>
                    <Button
                        type="submit"
                        :disabled="processing"
                        class="m-auto h-10 w-10 cursor-pointer rounded-full bg-primary text-white hover:bg-primary/90 focus:ring-2 focus:ring-primary/50 disabled:pointer-events-none disabled:opacity-50"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 24 24">
                            <path fill="#ffffff" d="M13 7.828V20h-2V7.828l-5.364 5.364l-1.414-1.414L12 4l7.778 7.778l-1.414 1.414z" />
                        </svg>
                    </Button>
                </div>
                <div v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</div>
            </form>
        </div>
    </AppLayout>
</template>
