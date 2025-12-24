import React from "react"
// import ReactMarnkdown from "react-markdown"
// import remarkGfm from "remark-gfm"

interface MarkdownRendererProps {
    content: string
}

export function MarkdownRenderer({ content }: MarkdownRendererProps) {
    return (
        <div
            className="
        prose prose-invert
        max-w-full                    /* don't exceed parent width */
        break-words                   /* wrap long words/URLs */
        space-y-4
        prose-headings:font-bold
        prose-a:text-primary
        prose-code:text-primary
        prose-pre:bg-muted
        prose-pre:p-4
        prose-pre:rounded-md
        prose-pre:overflow-x-auto     /* horizontal scroll for huge code blocks */
        prose-pre:whitespace-pre-wrap /* allow wrapping inside code */
        prose-img:max-w-full prose-img:h-auto /* images stay inside */
      "
        >
            {/* <ReactMarkdown
                plugins={[remarkGfm]}
            >
                {content}
            </ReactMarkdown> */}
        </div>
    )
}