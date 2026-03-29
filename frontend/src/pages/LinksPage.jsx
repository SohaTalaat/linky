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

  const fetchLinks = async () => {
    try {
      const res = await getLinks();
      setLinks(res.data.data);
    } catch {
      setError("Failed to Load Links");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLinks();
  }, []);

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

      {/* List */}
      {links.length === 0 && <p>No Links Yet</p>}

      {links.map((link) => (
        <LinkCard
          key={link.id}
          link={link}
          onDelete={handleDelete}
          onToggleFavorite={handleToggleFavorite}
        />
      ))}
    </div>
  );
}
