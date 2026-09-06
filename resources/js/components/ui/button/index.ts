import type { VariantProps } from 'class-variance-authority'
import { cva } from 'class-variance-authority'

export { default as Button } from './Button.vue'

export const buttonVariants = cva(
  'focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border border-transparent bg-clip-padding text-xs font-semibold group/button inline-flex shrink-0 items-center justify-center whitespace-nowrap transition-all outline-none select-none disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0',
  {
    variants: {
      variant: {
        default: 'bg-primary text-primary-foreground hover:bg-[#04407D] shadow-xs',
        secondary: 'bg-secondary text-secondary-foreground hover:bg-[#DDB05A] shadow-xs',
        outline: 'border-primary border bg-background text-primary hover:bg-slate-50 shadow-2xs',
        ghost: 'hover:bg-muted hover:text-foreground',
        destructive: 'bg-destructive text-destructive-foreground hover:bg-destructive/90 shadow-xs',
        link: 'text-primary underline-offset-4 hover:underline',
      },
      size: {
        'default': 'h-9 px-4 py-2 gap-1.5 text-xs',
        'xs': 'h-6 px-2 text-[11px] gap-1',
        'sm': 'h-8 px-3 text-xs gap-1.5',
        'lg': 'h-10 px-5 text-sm gap-2',
        'icon': 'size-9',
        'icon-xs': 'size-6',
        'icon-sm': 'size-8',
        'icon-lg': 'size-10',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  },
)
export type ButtonVariants = VariantProps<typeof buttonVariants>
