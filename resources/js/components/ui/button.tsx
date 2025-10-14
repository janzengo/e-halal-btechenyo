import * as React from "react"
import { Slot } from "@radix-ui/react-slot"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg text-sm font-medium transition-colors duration-200 cursor-pointer disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 [&_svg]:shrink-0 outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary/20 active:scale-[0.98]",
  {
    variants: {
      variant: {
        // Primary - Main brand color
        default:
          "bg-green-600 text-white shadow-sm hover:bg-green-700 border border-green-600",
        
        // Secondary - Dark blue
        secondary:
          "bg-blue-900 text-white shadow-sm hover:bg-blue-800 border border-blue-900",
        
        // Accent 1 - Yellow
        accent:
          "bg-yellow-400 text-gray-900 shadow-sm hover:bg-yellow-500 border border-yellow-400",
        
        // Accent 2 - Orange
        warning:
          "bg-orange-500 text-white shadow-sm hover:bg-orange-600 border border-orange-500",
        
        white:
          "bg-white text-green-600 shadow-sm hover:bg-gray-100 border border-white",
        // Outline variants
        outline:
          "border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-400 hover:text-gray-900",
            
        outlinePrimary:
          "border border-green-600 bg-transparent text-green-600 shadow-sm hover:bg-green-600 hover:text-white",

        outlineSecondary:
          "border border-blue-900 bg-transparent text-blue-900 shadow-sm hover:bg-blue-900 hover:text-white",
        
        outlineWhite:
          "border border-white bg-transparent text-white shadow-sm hover:bg-white hover:text-green-600",
        
        // Ghost variants
        ghost:
          "text-gray-700 hover:bg-gray-100 hover:text-gray-900",
        
        ghostPrimary:
          "text-green-600 hover:bg-green-50 hover:text-green-700",
        
        ghostSecondary:
          "text-blue-900 hover:bg-blue-50 hover:text-blue-800",
        
        // Destructive
        destructive:
          "bg-red-600 text-white shadow-sm hover:bg-red-700 border border-red-600",
        
        // Link
        link:
          "text-green-600 underline-offset-4 hover:underline hover:text-green-700",
      },
      size: {
        sm: "h-8 px-3 text-xs",
        default: "h-9 px-4 text-sm",
        icon: "h-9 w-9",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

function Button({
  className,
  variant,
  size,
  asChild = false,
  ...props
}: React.ComponentProps<"button"> &
  VariantProps<typeof buttonVariants> & {
    asChild?: boolean
  }) {
  const Comp = asChild ? Slot : "button"

  return (
    <Comp
      data-slot="button"
      className={cn(buttonVariants({ variant, size, className }))}
      {...props}
    />
  )
}

export { Button, buttonVariants }