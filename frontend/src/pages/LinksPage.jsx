import { useEffect, useState } from "react";
import { getLinks, createLink } from "../api/links";

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

      <ul>
        {links.map((link) => (
          <li key={link.id}>
            <a href={link.url} target="_blank">
              {link.title || link.url}
            </a>
          </li>
        ))}
      </ul>
    </div>
  );
}
