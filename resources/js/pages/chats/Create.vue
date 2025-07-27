<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { marked } from 'marked';
import { ref } from 'vue';

const message = ref('');
const processing = ref(false);
const error = ref('');
const isLoading = ref(false);

const messages = ref<{ from: 'user' | 'ia'; content: string }[]>([]);

const submit = async () => {
    if (!message.value.trim()) return;

    messages.value.push({ from: 'user', content: message.value });
    processing.value = true;
    error.value = '';
    isLoading.value = true;

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
            messages.value.push({ from: 'ia', content: data.reply });
        } else {
            error.value = 'Error del servidor';
            messages.value.push({ from: 'ia', content: 'Error al procesar la respuesta.' });
        }
    } catch (e) {
        error.value = 'Error de conexión';
        messages.value.push({ from: 'ia', content: 'No se pudo conectar al servidor.' });
    } finally {
        isLoading.value = false;
        message.value = '';
        processing.value = false;
    }
};
</script>

<template>
    <Head title="Nuevo Chat" />

    <AppLayout>
        <div class="flex h-[calc(100vh-4rem)] flex-col bg-white text-gray-900">
            <div class="flex-1 space-y-4 overflow-y-auto p-6">
                <div v-if="messages.length === 0 && !isLoading" class="text-sm text-gray-500">Sin mensajes aún</div>

                <div
                    v-for="(msg, index) in messages"
                    :key="index"
                    class="h-fit rounded-lg p-4 text-sm"
                    :class="msg.from === 'ia' ? 'self-start bg-gray-100 text-gray-700' : 'self-end bg-neutral-800 text-white'"
                    v-html="marked.parse(msg.content)"
                />

                <div v-if="isLoading" class="text-sm text-gray-500 bg-gray-100 self-start px-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 24 24">
                        <circle cx="18" cy="12" r="0" fill="#333333">
                            <animate attributeName="r" begin=".67" dur="1.5s" values="0;2;0;0" repeatCount="indefinite" />
                        </circle>
                        <circle cx="12" cy="12" r="0" fill="#333333">
                            <animate attributeName="r" begin=".33" dur="1.5s" values="0;2;0;0" repeatCount="indefinite" />
                        </circle>
                        <circle cx="6" cy="12" r="0" fill="#333333">
                            <animate attributeName="r" begin="0" dur="1.5s" values="0;2;0;0" repeatCount="indefinite" />
                        </circle>
                    </svg>
                </div>
            </div>

            <form @submit.prevent="submit" class="border-t border-gray-300 bg-gray-50 p-4">
                <div class="flex items-end gap-2">
                    <textarea
                        v-model="message"
                        placeholder="Pregunta lo que quieras"
                        rows="2"
                        class="max-h-32 w-full resize-none rounded-md border border-gray-300 bg-white px-3 py-2 text-sm placeholder-gray-400 focus:ring-1 focus:ring-neutral-500 focus:outline-none"
                    ></textarea>
                    <Button type="submit" :disabled="processing" class="m-auto h-10 w-10 rounded-full bg-neutral-800 p-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M13 7.828V20h-2V7.828l-5.364 5.364l-1.414-1.414L12 4l7.778 7.778l-1.414 1.414z" />
                        </svg>
                    </Button>
                </div>
                <div v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</div>
            </form>
        </div>
    </AppLayout>
</template>
