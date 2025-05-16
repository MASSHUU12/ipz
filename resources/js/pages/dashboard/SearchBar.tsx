import React from "react";
import {
  AppBar,
  Toolbar,
  TextField,
  InputAdornment,
  IconButton,
  useMediaQuery,
  styled,
  Autocomplete,
  CircularProgress,
} from "@mui/material";
import {
  Menu as MenuIcon,
  Search as SearchIcon,
  Notifications,
} from "@mui/icons-material";
import { useAddressSuggestions } from "@/hooks/useAddressSuggestions";

interface Props {
  searchValue: string;
  onSearchChange: (v: string) => void;
  onSearchSubmit: () => void;
  onMenuClick?: () => void;
}

const StyledAppBar = styled(AppBar)({
  backgroundColor: "#1e1e1e",
});

const StyledTextField = styled(TextField)({
  backgroundColor: "#2e2e2e",
  borderRadius: 4,
  "& .MuiInputBase-input": {
    color: "#fff",
  },
  "& .MuiOutlinedInput-notchedOutline": {
    borderColor: "transparent",
  },
});

export const SearchBar: React.FC<Props> = ({
  searchValue,
  onSearchChange,
  onSearchSubmit,
  onMenuClick,
}) => {
  const isMobile = useMediaQuery("(max-width:900px)");
  const { options, loading } = useAddressSuggestions(searchValue);

  return (
    <StyledAppBar position="static">
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

        <Autocomplete
          freeSolo
          disableClearable
          fullWidth
          options={options}
          loading={loading}
          inputValue={searchValue}
          onInputChange={(_, value, reason) => {
            if (reason === "input") onSearchChange(value);
          }}
          onChange={(_, value) => {
            if (typeof value === "string") {
              onSearchChange(value);
            }
          }}
          onKeyDown={e => e.key === "Enter" && onSearchSubmit()}
          renderInput={params => (
            <StyledTextField
              {...params}
              placeholder="Search city"
              size="small"
              variant="outlined"
              InputProps={{
                ...params.InputProps,
                endAdornment: (
                  <>
                    {loading ? (
                      <CircularProgress
                        size={20}
                        sx={{ color: "#fff", mr: 1 }}
                      />
                    ) : null}
                    <InputAdornment position="end">
                      <IconButton
                        onClick={onSearchSubmit}
                        sx={{ color: "#fff" }}>
                        <SearchIcon />
                      </IconButton>
                    </InputAdornment>
                  </>
                ),
              }}
            />
          )}
        />

        <IconButton sx={{ color: "#fff" }}>
          <Notifications />
        </IconButton>
      </Toolbar>
    </StyledAppBar>
  );
};
