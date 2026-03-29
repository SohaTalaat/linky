export default function LinkCard({ link, onDelete, onToggleFavorite }) {
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
      <button onClick={() => onDelete(link.id)}>Delete</button>
    </div>
  );
}
