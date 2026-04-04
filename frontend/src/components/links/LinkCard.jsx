import { useState } from "react";

export default function LinkCard({
  link,
  onDelete,
  onToggleFavorite,
  onUpdateStatus,
  onAddTag,
  onRemoveTag,
}) {
  const [newTag, setNewTag] = useState("");

  const statusStyles = {
    saved: "bg-slate-100 text-slate-700 border-slate-200",
    reading: "bg-amber-100 text-amber-800 border-amber-200",
    done: "bg-emerald-100 text-emerald-800 border-emerald-200",
  };

  const host = (() => {
    try {
      return new URL(link.url).hostname.replace(/^www\./, "");
    } catch {
      return null;
    }
  })();

  const handleAddTag = () => {
    if (!newTag.trim()) return;

    onAddTag(link.id, newTag);
    setNewTag("");
  };

  return (
    <article className="mb-4 rounded-2xl border border-slate-200 bg-white/95 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
      <div className="flex items-start gap-3">
        <button
          className={`text-2xl leading-none transition ${
            link.is_favourite
              ? "text-amber-500 hover:text-amber-600"
              : "text-slate-300 hover:text-amber-400"
          }`}
          onClick={() => onToggleFavorite(link)}
          aria-label={link.is_favourite ? "Remove favorite" : "Mark favorite"}
          title={link.is_favourite ? "Favorited" : "Mark as favorite"}
        >
          {link.is_favourite ? "★" : "☆"}
        </button>

        <div className="min-w-0 flex-1">
          <a
            href={link.url}
            target="_blank"
            rel="noopener noreferrer"
            className="block truncate text-lg font-semibold text-slate-800 hover:text-blue-700 hover:underline"
            title={link.title || link.url}
          >
            {link.title || link.url}
          </a>
          <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
            <span className="truncate">{host || "External link"}</span>
            <span className="text-slate-300">•</span>
            <span
              className={`rounded-full border px-2 py-0.5 font-medium ${
                statusStyles[link.status] ||
                "bg-slate-100 text-slate-700 border-slate-200"
              }`}
            >
              {link.status}
            </span>
          </div>
        </div>
      </div>
      {link.notes && (
        <p className="mt-3 rounded-xl bg-slate-50 px-3 py-2 text-sm leading-relaxed text-slate-600">
          {link.notes}
        </p>
      )}
      {/* Tags */}
      <div className="mt-3 flex flex-wrap gap-2">
        {link.tags.map((tag) => (
          <span
            key={tag.id}
            className="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700"
          >
            #{tag.name}
            <button
              onClick={() => onRemoveTag(link.id, tag.id)}
              className="text-blue-400 hover:text-red-500"
            >
              X
            </button>
          </span>
        ))}
      </div>
      {/*Add Tag*/}
      <div className="mt-3 flex gap-2">
        <input
          type="text"
          placeholder="Add Tag..."
          value={newTag}
          onChange={(e) => setNewTag(e.target.value)}
          className="flex-1 border rounded-lg px-2 py-1 text-sm"
        />
        <button
          onClick={handleAddTag}
          className="bg-blue-500 text-white px-3 rounded-lg text-sm hover:bg-blue-600"
        >
          Add
        </button>
      </div>
      {/*  Actions */}
      <div className="mt-4 flex items-center gap-2">
        <select
          value={link.status}
          onChange={(e) => onUpdateStatus(link, e.target.value)}
          className="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-700 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
        >
          <option value="saved">Saved</option>
          <option value="reading">Reading</option>
          <option value="done">Done</option>
        </select>

        <button
          onClick={() => onDelete(link.id)}
          className="ml-auto rounded-lg border border-rose-200 px-3 py-1.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50 hover:text-rose-700"
        >
          Delete
        </button>
      </div>
    </article>
  );
}
