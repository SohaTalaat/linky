export default function LinkCard({
  link,
  onDelete,
  onToggleFavorite,
  onUpdateStatus,
}) {
  return (
    <div className="bg-white p-4 rounded-xl shadow-sm border mb-4">
      <a
        href={link.url}
        target="_blank"
        rel="noopener"
        noreferrer
        className="font-semibold text-blue-600 hover:underline"
      >
        <strong>{link.title || link.url}</strong>
      </a>
      {/* Notes */}

      {link.notes && <p className="text-gray-600 mt-1">{link.notes}</p>}

      {/* Actions */}

      <div className="flex items-center gap-3 mt-3">
        {/* Favorite */}
        <button
          className={`text-xl ${link.is_favourite ? "text-yellow-500" : "text-gray-400"}`}
          onClick={() => onToggleFavorite(link)}
        >
          {link.is_favourite ? "★" : "☆"}
        </button>

        {/* Status */}

        <select
          value={link.status}
          onChange={(e) => onUpdateStatus(link, e.target.value)}
          className="border rounded px-2 py-1 text-sm"
        >
          <option value="saved">Saved</option>
          <option value="reading">Reading</option>
          <option value="done">Done</option>
        </select>

        {/* <span
          className={`px-2 py-1 text-xs rounded-full
          ${link.status === "saved" && "bg-gray-200 text-gray-700"}
          ${link.status === "reading" && "bg-yellow-100 text-yellow-700"}
          ${link.status === "done" && "bg-green-100 text-green-700"}
        `}
        >
          {link.status}
        </span> */}
        <button
          onClick={() => onDelete(link.id)}
          className="text-red-500 hover:text-red-700 text-sm ml-auto"
        >
          Delete
        </button>
      </div>
    </div>
  );
}
