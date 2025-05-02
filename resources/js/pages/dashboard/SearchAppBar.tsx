import React from "react";
import { suggestAddresses } from "@/api/addressApi";
import {
  AppBar,
  Toolbar,
  TextField,
  InputAdornment,
  IconButton,
  useMediaQuery,
} from "@mui/material";
import {
  Menu as MenuIcon,
  Search as SearchIcon,
  Notifications,
} from "@mui/icons-material";
import { useState, useEffect } from "react";
import { Paper, List, ListItem, ListItemButton, ListItemText } from "@mui/material";

interface Props {
  searchValue: string;
  onSearchChange: (v: string) => void;
  onSearchSubmit: () => void;
  onMenuClick?: () => void;
}

export const SearchAppBar: React.FC<Props> = ({
  searchValue,
  onSearchChange,
  onSearchSubmit,
  onMenuClick,
}) => {
  const isMobile = useMediaQuery("(max-width:900px)");

    const [suggestions, setSuggestions] = useState<string[]>([]);
    const [open, setOpen] = useState(false);

    function useDebounce(value: string, delay = 300) {
        const [debounced, setDebounced] = useState(value);
        useEffect(() => {
            const handler = setTimeout(() => setDebounced(value), delay);
            return () => clearTimeout(handler);
        }, [value, delay]);
        return debounced;
    }

    const debouncedSearch = useDebounce(searchValue, 300);
    const token = localStorage.getItem("token") || "";


    useEffect(() => {
        if (!debouncedSearch.trim() || !token) {
            setSuggestions([]);
            setOpen(false);
            return;
        }

        suggestAddresses({ token, q: debouncedSearch })
            .then(data => {
                if (data) {
                    setSuggestions(data.suggestions);
                    setOpen(data.suggestions.length > 0);
                }
            })
            .catch(console.error);
    }, [debouncedSearch, token]);



    return (
    <AppBar position="static" sx={{ backgroundColor: "#1e1e1e" }}>
      <Toolbar sx={{ justifyContent: "space-between" }}>
        {isMobile && (
          <IconButton
            edge="start"
            color="inherit"
            onClick={onMenuClick}
            sx={{ mr: 1 }}>
            <MenuIcon />
          </IconButton>
        )}

          <div style={{ position: 'relative', flex: 1, marginRight: 16 }}>
              <TextField
                  fullWidth
                  placeholder="Search city"
                  size="small"
                  variant="outlined"
                  value={searchValue}
                  onChange={e => onSearchChange(e.target.value)}
                  onKeyDown={e => e.key === "Enter" && onSearchSubmit()}
                  sx={{ backgroundColor: "#2e2e2e", borderRadius: 1 }}
                  InputProps={{
                      endAdornment: (
                          <InputAdornment position="end">
                              <IconButton onClick={onSearchSubmit} sx={{ color: "#fff" }}>
                                  <SearchIcon />
                              </IconButton>
                          </InputAdornment>
                      ),
                      style: { color: "#fff" },
                  }}
              />

              {open && (
                  <Paper
                      sx={{
                          position: 'absolute',
                          top: '100%',
                          left: 0,
                          right: 0,
                          zIndex: 10,
                          maxHeight: 300,
                          overflowY: 'auto',
                      }}
                  >
                      <List dense>
                          {suggestions.map((item, idx) => (
                              <ListItem key={idx} disablePadding>
                                  <ListItemButton
                                      onClick={() => {
                                          onSearchChange(item);
                                          setOpen(false);
                                      }}
                                  >
                                      <ListItemText primary={item} />
                                  </ListItemButton>
                              </ListItem>
                          ))}
                      </List>
                  </Paper>
              )}
          </div>


        <div>
          <IconButton sx={{ color: "#fff" }}>
            <Notifications />
          </IconButton>
        </div>
      </Toolbar>
    </AppBar>
  );
};
