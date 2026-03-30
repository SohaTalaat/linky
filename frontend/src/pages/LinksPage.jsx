import { useEffect, useState } from "react";
import { getLinks, createLink, deleteLink } from "../api/links";
import LinkCard from "../components/links/LinkCard";
import { updateLink } from "../api/links";
import { getTags } from "../api/tags";

export default function LinksPage() {
  const [links, setLinks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const [url, setUrl] = useState("");
  const [creating, setCreating] = useState(false);

  const [search, setSearch] = useState("");
  const [status, setStatus] = useState("");

  const [favoriteOnly, setFavoriteOnly] = useState(false);

  const [tags, setTags] = useState([]);
  const [selectedTag, setSelectedTag] = useState("");

  const fetchLinks = async () => {
    try {
      const res = await getLinks({
        search: search || undefined,
        status: status || undefined,
        favourite: favoriteOnly ? true : undefined,
      });
      setLinks(res.data.data);
    } catch {
      setError("Failed to Load Links");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const delay = setTimeout(() => {
      fetchLinks();
    }, 400);

    return () => clearTimeout(delay);
  }, [search, status, favoriteOnly]);

  useEffect(() => {
    getTags().then((res) => setTags(res.data.data));
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
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-2xl mx-auto">
        <h1 className="text-3xl font-bold mb-6">My Links</h1>

        <form onSubmit={handleCreate} className="flex gap-2 mb-6">
          <input
            type="text"
            placeholder="Paste your link..."
            value={url}
            onChange={(e) => setUrl(e.target.value)}
            className="flex-1 border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:blue-400"
          />
          <button
            type="submit"
            disabled={creating}
            className="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50 transition"
          >
            {creating ? "Adding..." : "Add"}
          </button>
        </form>

        {/* Search */}

        <div className="flex gap-3 mb-6 items-center flex-wrap">
          <input
            type="text"
            placeholder="Search..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="border rounded-lg px-3 py-2"
          />

          {/* Status filter */}

          <select
            value={status}
            onChange={(e) => setStatus(e.target.value)}
            className="border rounded-lg px-3 py-2"
          >
            <option value="">All</option>
            <option value="saved">Saved</option>
            <option value="reading">Reading</option>
            <option value="done">Done</option>
          </select>
        </div>

        <label className="flex items-center gap-2 mb-5">
          <input
            type="checkbox"
            checked={favoriteOnly}
            onChange={(e) => setFavoriteOnly(e.target.checked)}
          />
          Favorites Only
        </label>

        <div className="flex gap-2 mb-4">
          <input
            placeholder="Search..."
            className="px-3 py-2 border rounded-lg"
          />

          <select
            value={selectedTag}
            onChange={(e) => setSelectedTag(e.target.value)}
            className="px-3 py-2 border rounded-lg"
          >
            <option value="">All</option>
            {tags.map((tag) => (
              <option key={tag.id} value={tag.name}>
                {tag.name}
              </option>
            ))}
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
    </div>
  );
}
