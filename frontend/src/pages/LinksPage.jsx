import { useEffect, useState } from "react";
import { getLinks, createLink, deleteLink } from "../api/links";
import LinkCard from "../components/links/LinkCard";
import { updateLink } from "../api/links";

export default function LinksPage() {
  const [links, setLinks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const [url, setUrl] = useState("");
  const [creating, setCreating] = useState(false);

  const [search, setSearch] = useState("");
  const [status, setStatus] = useState("");

  const fetchLinks = async () => {
    try {
      const res = await getLinks({
        search: search || undefined,
        status: status || undefined,
      });
      setLinks(res.data.data);
    } catch {
      setError("Failed to Load Links");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLinks();
  }, [search, status]);

  const handleCreate = async (e) => {
    e.preventDefault();

    if (!url.trim()) return;

    setCreating(true);

    try {
      const res = await createLink({ url });

      setLinks((prev) => [res.data.data, ...prev]);
      setUrl("");
    } catch (err) {
      console.log(err);
      alert("Failed to create link");
    } finally {
      setCreating(false);
    }
  };

  const handleDelete = async (id) => {
    const confirmed = confirm("Delete This Link?");

    if (!confirmed) return;

    try {
      await deleteLink(id);

      setLinks((prev) => prev.filter((l) => l.id !== id));
    } catch {
      alert("Failed to delete");
    }
  };

  const handleToggleFavorite = async (link) => {
    const newValue = !link.is_favourite;

    try {
      await updateLink(link.id, {
        is_favourite: newValue,
      });

      setLinks((prev) =>
        prev.map((l) =>
          l.id === link.id ? { ...l, is_favourite: newValue } : l,
        ),
      );
    } catch {
      alert("Failed to update Favorite");
    }
  };

  const handleUpdateStatus = async (link, newStatus) => {
    try {
      await updateLink(link.id, {
        status: newStatus,
      });

      setLinks((prev) =>
        prev.map((l) => (l.id === link.id ? { ...l, status: newStatus } : l)),
      );
    } catch {
      alert("Failed to update status");
    }
  };

  if (loading) return <p>Loading links...</p>;
  if (error) return <p>{error}</p>;

  return (
    <div>
      <h1>My Links</h1>

      <form onSubmit={handleCreate}>
        <input
          type="text"
          placeholder="Paste your link..."
          value={url}
          onChange={(e) => setUrl(e.target.value)}
        />
        <button type="submit" disabled={creating}>
          {creating ? "Adding..." : "Add"}
        </button>
      </form>

      <div style={{ margin: "20px 0" }}>
        {/* Search */}

        <input
          type="text"
          placeholder="Search..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
        />

        {/* Status filter */}

        <select value={status} onChange={(e) => setStatus(e.target.value)}>
          <option value="">All</option>
          <option value="saved">Saved</option>
          <option value="reading">Reading</option>
          <option value="done">Done</option>
        </select>
      </div>

      {/* List */}
      {links.length === 0 && <p>No Links Yet</p>}

      {links.map((link) => (
        <LinkCard
          key={link.id}
          link={link}
          onDelete={handleDelete}
          onToggleFavorite={handleToggleFavorite}
          onUpdateStatus={handleUpdateStatus}
        />
      ))}
    </div>
  );
}
