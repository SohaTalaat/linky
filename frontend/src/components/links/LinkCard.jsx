export default function LinkCard({
  link,
  onDelete,
  onToggleFavorite,
  onUpdateStatus,
}) {
  const statusColor = {
    saved: "grey",
    reading: "orange",
    done: "green",
  };

  return (
    <div
      style={{
        border: "1px solid #ddd",
        padding: "10px",
        marginBottom: "10px",
      }}
    >
      <a href={link.url} target="_blank" rel="noopener" noreferrer>
        <strong>{link.title || link.url}</strong>
      </a>
      <p>{link.notes}</p>
      {/* Favorite */}
      <button
        style={{ fontSize: "20px", color: link.is_favourite ? "gold" : "grey" }}
        onClick={() => onToggleFavorite(link)}
      >
        {link.is_favourite ? "★" : "☆"}
      </button>

      {/* Status */}

      <select
        value={link.status}
        onChange={(e) => onUpdateStatus(link, e.target.value)}
        style={{
          padding: "4px 8px",
          borderRadius: "8px",
          backgroundColor: statusColor[link.status],
          color: "white",
          marginLeft: "10px",
        }}
      >
        <option value="saved">Saved</option>
        <option value="reading">Reading</option>
        <option value="done">Done</option>
      </select>
      <button onClick={() => onDelete(link.id)}>Delete</button>
    </div>
  );
}
