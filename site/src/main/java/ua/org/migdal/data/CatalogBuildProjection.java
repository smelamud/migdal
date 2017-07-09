package ua.org.migdal.data;

public interface CatalogBuildProjection {

    EntryType getEntryType();

    long getId();

    String getIdent();

    IdProjection getUp();

    String getCatalog();

    long getModbits();

}