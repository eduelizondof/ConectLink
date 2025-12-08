<script setup lang="ts">
import type { SliderRootEmits, SliderRootProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import {
    SliderRange,
    SliderRoot,
    SliderThumb,
    SliderTrack,
    useForwardPropsEmits,
} from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = withDefaults(
    defineProps<SliderRootProps & { class?: HTMLAttributes['class'] }>(),
    {
        orientation: 'horizontal',
    }
)
const emits = defineEmits<SliderRootEmits>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props
    return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
    <SliderRoot
        v-bind="forwarded"
        :class="cn(
            'relative flex w-full touch-none select-none items-center',
            props.orientation === 'vertical' && 'flex-col h-full w-auto',
            props.class
        )"
    >
        <SliderTrack
            :class="cn(
                'relative grow overflow-hidden rounded-full bg-primary/20',
                props.orientation === 'horizontal' ? 'h-1.5 w-full' : 'w-1.5 h-full'
            )"
        >
            <SliderRange
                :class="cn(
                    'absolute bg-primary',
                    props.orientation === 'horizontal' ? 'h-full' : 'w-full'
                )"
            />
        </SliderTrack>
        <SliderThumb
            v-for="(_, index) in (props.modelValue ?? [props.defaultValue ?? 0])"
            :key="index"
            :class="cn(
                'block h-4 w-4 rounded-full border border-primary/50 bg-background shadow transition-colors',
                'focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
                'disabled:pointer-events-none disabled:opacity-50'
            )"
        />
    </SliderRoot>
</template>
