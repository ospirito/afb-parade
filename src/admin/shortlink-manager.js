import { render, useState, useEffect } from "@wordpress/element";
import { Button, Spinner, Notice, TextControl } from "@wordpress/components";
import apiFetch from "@wordpress/api-fetch";

const ShortlinkManager = () => {
	const [shortlinks, setShortlinks] = useState([]);
	const [isLoading, setIsLoading] = useState(true);
	const [notice, setNotice] = useState(null);
	const [editingId, setEditingId] = useState(null);
	const [copiedId, setCopiedId] = useState(null);
	const [slug, setSlug] = useState("");
	const defaultParams = window.AFBShortlinkData?.defaultParams || [{ key: "", value: "" }];
	const [queryParams, setQueryParams] = useState(defaultParams);

	const baseDomain = window.AFBShortlinkData?.baseDomain || "/s/";

	const fetchShortlinks = async () => {
		setIsLoading(true);
		try {
			const data = await apiFetch({ path: "/afb-parade/v1/shortlinks" });
			setShortlinks(data);
		} catch (err) {
			console.error(err);
			setNotice({ status: "error", message: "Failed to load shortlinks." });
		}
		setIsLoading(false);
	};

	useEffect(() => {
		fetchShortlinks();
	}, []);

	const saveShortlink = async () => {
		setIsLoading(true);
		setNotice(null);

		const queryString = queryParams
			.filter((p) => p.key.trim() !== '' && p.value.trim() !== '')
			.map((p) => `${encodeURIComponent(p.key.trim())}=${encodeURIComponent(p.value.trim())}`)
			.join('&');

		try {
			await apiFetch({
				path: `/afb-parade/v1/shortlinks/update/${editingId}`,
				method: "POST",
				data: { slug, query_params: queryString },
			});
			setNotice({ status: "success", message: "Shortlink updated!" });
			setEditingId(null);
			setSlug("");
			setQueryParams(defaultParams);
			fetchShortlinks();
		} catch (err) {
			setNotice({ status: "error", message: err.message || "Error updating shortlink." });
			setIsLoading(false);
		}
	};

	const deleteShortlink = async (id) => {
		if (!confirm("Are you sure you want to delete this shortlink?")) return;
		setIsLoading(true);
		try {
			await apiFetch({
				path: `/afb-parade/v1/shortlinks?id=${id}`,
				method: "DELETE",
			});
			setNotice({ status: "success", message: "Shortlink deleted." });
			fetchShortlinks();
		} catch (err) {
			setNotice({ status: "error", message: err.message || "Error deleting." });
			setIsLoading(false);
		}
	};

	const startEditing = (link) => {
		setEditingId(link.id);
		setSlug(link.slug);
		if (link.query_params) {
			const pairs = link.query_params.split("&").map((pair) => {
				const [key, value] = pair.split("=").map(decodeURIComponent);
				return { key, value };
			});
			setQueryParams(pairs);
		} else {
			setQueryParams([{ key: "", value: "" }]);
		}
	};

	const copyToClipboard = (linkId, text) => {
		const handleSuccess = () => {
			setCopiedId(linkId);
			setTimeout(() => { if (setCopiedId) setCopiedId(null); }, 2000);
			setNotice({ status: "success", message: "Link copied to clipboard!" });
			setTimeout(() => { if (setNotice) setNotice(null); }, 2000);
		};

		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).then(handleSuccess).catch(() => {
				fallbackCopy(text, handleSuccess);
			});
		} else {
			fallbackCopy(text, handleSuccess);
		}
	};

	const fallbackCopy = (text, callback) => {
		const textArea = document.createElement("textarea");
		textArea.value = text;
		textArea.style.position = "fixed";
		textArea.style.left = "-9999px";
		textArea.style.top = "0";
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();
		try {
			const successful = document.execCommand('copy');
			if (successful && callback) callback();
		} catch (err) {
			console.error('Fallback copy failed', err);
		}
		document.body.removeChild(textArea);
	};

	return (
		<div style={{ marginTop: "20px" }}>
			{notice && (
				<Notice status={notice.status} onRemove={() => setNotice(null)}>
					{notice.message}
				</Notice>
			)}

			{editingId && (
				<div style={{ padding: "20px", border: "1px solid #ccc", marginBottom: "20px", background: "#fff" }}>
					<h3>Edit Shortlink</h3>
					<TextControl label="Slug" value={slug} onChange={setSlug} __nextHasNoMarginBottom />
					<div>
						<strong>Query Parameters</strong>
						{queryParams.map((param, index) => (
							<div key={index} style={{ display: "flex", gap: "10px", marginTop: "10px" }}>
								<TextControl placeholder="Key" value={param.key} onChange={(val) => {
									const newParams = [...queryParams];
									newParams[index].key = val;
									setQueryParams(newParams);
								}} __nextHasNoMarginBottom />
								<TextControl placeholder="Value" value={param.value} onChange={(val) => {
									const newParams = [...queryParams];
									newParams[index].value = val;
									setQueryParams(newParams);
								}} __nextHasNoMarginBottom />
								<Button variant="link" isDestructive onClick={() => setQueryParams(queryParams.filter((_, i) => i !== index))}>&times;</Button>
							</div>
						))}
						<Button variant="secondary" onClick={() => setQueryParams([...queryParams, { key: "", value: "" }])}>+ Add Parameter</Button>
					</div>
					<div style={{ marginTop: "20px", display: "flex", gap: "10px" }}>
						<Button variant="primary" onClick={saveShortlink}>Save Changes</Button>
						<Button variant="secondary" onClick={() => {
							setEditingId(null);
							setSlug("");
							setQueryParams(defaultParams);
						}}>Cancel</Button>
					</div>
				</div>
			)}

			<table className="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Short URL</th>
						<th>Target Page</th>
						<th>Query Params</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					{shortlinks.map((link) => (
						<tr key={link.id}>
							<td 
								onClick={() => copyToClipboard(link.id, `${baseDomain}${link.slug}`)}
								style={{ cursor: "pointer" }}
								title="Click to copy shortlink"
							>
								<div style={{ display: "flex", flexDirection: "column" }}>
									<strong>{baseDomain}{link.slug}</strong>
									{link.query_params && (
										<div style={{ fontSize: "11px", color: "#666", marginTop: "8px", borderTop: "1px solid #eee", paddingTop: "8px" }}>
											{link.query_params.split('&').map(pair => {
												const [k, v] = pair.split('=').map(decodeURIComponent);
												return (
													<div key={pair} style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '2px' }}>
														<span style={{ fontWeight: '600', color: '#888' }}>{k}</span>
														<span style={{ color: '#444' }}>{v}</span>
													</div>
												);
											})}
										</div>
									)}
									{copiedId === link.id && (
										<span style={{ fontSize: "10px", color: "#46b450", fontWeight: "bold", textTransform: "uppercase" }}>Copied!</span>
									)}
								</div>
							</td>
							<td><a href={link.target_url} target="_blank" rel="noreferrer">{link.target_post_title}</a></td>
							<td>{link.query_params ? `?${link.query_params}` : "-"}</td>
							<td>
								<div style={{ display: "flex", gap: "10px" }}>
									<Button variant="secondary" size="small" onClick={() => startEditing(link)}>Edit</Button>
									<Button variant="primary" isDestructive size="small" onClick={() => deleteShortlink(link.id)}>Delete</Button>
								</div>
							</td>
						</tr>
					))}
					{shortlinks.length === 0 && !isLoading && (
						<tr>
							<td colSpan="4">No shortlinks found.</td>
						</tr>
					)}
					{isLoading && (
						<tr>
							<td colSpan="4"><Spinner /> Loading...</td>
						</tr>
					)}
				</tbody>
			</table>
		</div>
	);
};

document.addEventListener("DOMContentLoaded", () => {
	const root = document.getElementById("afb-shortlink-manager-root");
	if (root) {
		render(<ShortlinkManager />, root);
	}
});
