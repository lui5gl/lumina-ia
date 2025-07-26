<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const message = ref('');
const processing = ref(false);
const error = ref('');
const messages = ref<string[]>([]);
const isLoading = ref(false);

const submit = async () => {
    if (!message.value.trim()) return;

    messages.value.push(`Tú: ${message.value}`);
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
            messages.value.push(`IA: ${data.reply}`);
        } else {
            error.value = 'Error del servidor';
            messages.value.push('IA: Error al procesar la respuesta.');
        }
    } catch (e) {
        error.value = 'Error de conexión';
        messages.value.push('IA: No se pudo conectar al servidor.');
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
        <div class="flex h-[calc(100vh-4rem)] flex-col">
            <div class="flex-1 space-y-4 overflow-y-auto p-6">
                <div v-if="messages.length === 0 && !isLoading" class="text-sm text-muted-foreground">Sin mensajes aún</div>
                <div
                    v-for="(msg, index) in messages"
                    :key="index"
                    class="text-sm whitespace-pre-line"
                    :class="{ 'text-muted-foreground': msg.startsWith('IA:') }"
                >
                    {{ msg }}
                </div>
                <div v-if="isLoading" class="text-sm text-muted-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                        <circle cx="18" cy="12" r="0" fill="#333333">
                            <animate
                                attributeName="r"
                                begin=".67"
                                calcMode="spline"
                                dur="1.5s"
                                keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8"
                                repeatCount="indefinite"
                                values="0;2;0;0"
                            />
                        </circle>
                        <circle cx="12" cy="12" r="0" fill="#333333">
                            <animate
                                attributeName="r"
                                begin=".33"
                                calcMode="spline"
                                dur="1.5s"
                                keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8"
                                repeatCount="indefinite"
                                values="0;2;0;0"
                            />
                        </circle>
                        <circle cx="6" cy="12" r="0" fill="#333333">
                            <animate
                                attributeName="r"
                                begin="0"
                                calcMode="spline"
                                dur="1.5s"
                                keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8"
                                repeatCount="indefinite"
                                values="0;2;0;0"
                            />
                        </circle>
                    </svg>
                </div>
            </div>

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
