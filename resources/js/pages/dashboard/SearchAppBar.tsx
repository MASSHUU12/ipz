import React from "react";
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

        <div>
          <IconButton sx={{ color: "#fff" }}>
            <Notifications />
          </IconButton>
        </div>
      </Toolbar>
    </AppBar>
  );
};
