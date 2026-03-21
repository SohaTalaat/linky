import { useEffect, useState } from "react";
import { getLinks } from "../api/links";

export default function LinksPage() {
  const [links, setLinks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const fetchLinks = async () => {
    setLoading(true);
    setError("");

    try {
      const res = await getLinks();
      setLinks(res.data.data);
    } catch (err) {
      setError("Failed to Load Links");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLinks();
  }, []);

  if (loading) return <p>Loading links...</p>;
  if (error) return <p>{error}</p>;

  return (
    <div>
      <h1>My Links</h1>
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
