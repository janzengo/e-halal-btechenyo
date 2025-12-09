import { Toaster as Sonner, ToasterProps } from "sonner"

const Toaster = ({ ...props }: ToasterProps) => {
  return (
    <Sonner
      theme="light"
      className="toaster group"
      style={
        {
          "--normal-bg": "var(--popover)",
          "--normal-text": "var(--popover-foreground)",
          "--normal-border": "var(--border)",
          zIndex: 9999,
        } as React.CSSProperties
      }
      toastOptions={{
        style: {
          zIndex: 9999,
        },
      }}
      {...props}
    />
  )
}

export { Toaster }
