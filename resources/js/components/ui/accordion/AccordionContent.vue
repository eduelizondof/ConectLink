<script setup lang="ts">
import type { AccordionContentProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { AccordionContent, useForwardProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<AccordionContentProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props
    return delegated
})

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
    <AccordionContent
        v-bind="forwardedProps"
        :class="cn(
            'overflow-hidden text-sm',
            'data-[state=closed]:animate-accordion-up data-[state=open]:animate-accordion-down',
            props.class
        )"
    >
        <div class="pb-4 pt-0">
            <slot />
        </div>
    </AccordionContent>
</template>
