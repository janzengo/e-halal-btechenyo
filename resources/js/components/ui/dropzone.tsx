"use client"

import * as React from "react"
import { useDropzone, type DropzoneOptions } from "react-dropzone"
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Upload, X, FileImage } from "lucide-react"

interface DropzoneProps extends Omit<DropzoneOptions, 'onDrop'> {
  onDrop?: (acceptedFiles: File[]) => void
  onError?: (error: Error) => void
  className?: string
  children?: React.ReactNode
}

const Dropzone = React.forwardRef<HTMLDivElement, DropzoneProps>(
  ({ onDrop, onError, className, children, ...props }, ref) => {
    const { getRootProps, getInputProps, isDragActive, acceptedFiles } = useDropzone({
      onDrop,
      onError,
      ...props,
    })

    return (
      <div
        ref={ref}
        {...getRootProps()}
        className={cn(
          "border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer transition-colors",
          isDragActive && "border-green-500 bg-green-50",
          className
        )}
      >
        <input {...getInputProps()} />
        {children || <DropzoneEmptyState isDragActive={isDragActive} />}
      </div>
    )
  }
)
Dropzone.displayName = "Dropzone"

interface DropzoneEmptyStateProps {
  isDragActive?: boolean
}

const DropzoneEmptyState = ({ isDragActive }: DropzoneEmptyStateProps) => (
  <div className="flex flex-col items-center gap-3">
    <Upload className="h-10 w-10 text-gray-400" />
    <div className="text-sm text-gray-600 space-y-1">
      {isDragActive ? (
        <>
          <p className="font-medium text-green-600">Drop the file here...</p>
          <p className="text-xs text-green-500">Release to upload</p>
        </>
      ) : (
        <>
          <p className="font-medium">Drag & drop a photo here</p>
          <p className="text-xs text-gray-500">or click to browse files</p>
          <p className="text-xs text-gray-400 mt-2">PNG, JPG, GIF up to 5MB</p>
        </>
      )}
    </div>
  </div>
)

interface DropzoneContentProps {
  files?: File[]
  onRemove?: (file: File) => void
  className?: string
}

const DropzoneContent = ({ files, onRemove, className }: DropzoneContentProps) => {
  if (!files || files.length === 0) return null

  return (
    <div className={cn("space-y-2", className)}>
      {files.map((file, index) => (
        <div
          key={index}
          className="flex items-center gap-2 p-2 bg-gray-50 rounded border"
        >
          <FileImage className="h-4 w-4 text-gray-500" />
          <span className="text-sm text-gray-700 flex-1 truncate">
            {file.name}
          </span>
          <span className="text-xs text-gray-500">
            {(file.size / 1024 / 1024).toFixed(2)} MB
          </span>
          {onRemove && (
            <Button
              type="button"
              variant="ghost"
              size="sm"
              onClick={() => onRemove(file)}
              className="h-6 w-6 p-0"
            >
              <X className="h-3 w-3" />
            </Button>
          )}
        </div>
      ))}
    </div>
  )
}

export { Dropzone, DropzoneEmptyState, DropzoneContent }
